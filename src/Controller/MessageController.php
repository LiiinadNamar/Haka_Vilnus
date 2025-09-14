<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Message;
use App\Repository\IssueRepository;
use App\Repository\MessageRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MessageController extends AbstractController
{
    #[Route('/messages/{issueId}', name: 'app_messages')]
    public function index(
        int $issueId,
        IssueRepository $issueRepository,
        MessageRepository $messageRepository
    ): Response {
        $issue = $issueRepository->find($issueId);

        if (!$issue) {
            throw $this->createNotFoundException('Issue not found');
        }

        $messages = $messageRepository->findBy(['Issue' => $issue], ['id' => 'ASC']);

        return $this->render('message/index.html.twig', [
            'issue' => $issue,
            'messages' => $messages,
        ]);
    }

    #[Route('/messages/{issueId}/send', name: 'app_send_message', methods: ['POST'])]
    public function sendMessage(
        int $issueId,
        Request $request,
        IssueRepository $issueRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $issue = $issueRepository->find($issueId);

        if (!$issue) {
            throw $this->createNotFoundException('Issue not found');
        }

        $content = $request->request->get('content');
        $is_external = filter_var($request->query->get('is_external', false), FILTER_VALIDATE_BOOLEAN);

        if (empty($content)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Message content cannot be empty'], 400);
            }
            $this->addFlash('error', 'Message content cannot be empty');
            return $this->redirectToRoute('app_messages', ['issueId' => $issueId]);
        }
        
        $message = new Message();
        $message->setContent($content);
        $message->setIssue($issue);
        $message->setIsCustomer($is_external);
        
        $entityManager->persist($message);
        $entityManager->flush();
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'messageId' => $message->getId()
            ]);
        }
        
        $this->addFlash('success', 'Message sent successfully');
        
        return $this->redirectToRoute('app_messages', ['issueId' => $issueId]);
    }

    #[Route('/messages/{issueId}/suggest', name: 'app_ai_suggest', methods: ['POST'])]
    public function aiSuggest(
        int $issueId,
        IssueRepository $issueRepository,
        MessageRepository $messageRepository,
        ApiService $apiService
    ): JsonResponse {
        $issue = $issueRepository->find($issueId);

        if (!$issue) {
            return new JsonResponse(['error' => 'Issue not found'], 404);
        }

        $messages = $messageRepository->findBy(['Issue' => $issue], ['id' => 'ASC']);

        if (empty($messages)) {
            return new JsonResponse(['error' => 'No messages found for this issue'], 400);
        }

        // Build conversation context
        $conversationContext = "Issue: " . $issue->getTitle() . "\n";
        if ($issue->getSummary()) {
            $conversationContext .= "Summary: " . $issue->getSummary() . "\n";
        }
        $conversationContext .= "\nConversation:\n";

        foreach ($messages as $message) {
            $sender = $message->isCustomer() ? 'Customer' : 'Support';
            $conversationContext .= $sender . ": " . $message->getContent() . "\n";
        }

        $prompt = 'json key is "response" ';
        $prompt .= 'keep in mind you are suggesting for the project host.com';
        $prompt .= 'for example instead of "adjust dns" generic say "go to host.com and adjust dns in domain settings"';
        $prompt .= "Using these messages, suggest a next answer for the customer:\n\n" . $conversationContext;

        try {
            $response = $apiService->run($prompt);
            $suggestion = $response['response'] ?? 'No suggestion available';

            return new JsonResponse([
                'success' => true,
                'suggestion' => $suggestion
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to get AI suggestion: ' . $e->getMessage()
            ], 500);
        }
    }
}
