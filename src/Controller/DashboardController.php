<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Message;
use App\Repository\IssueRepository;
use App\Repository\MessageRepository;
use App\Service\ProcessingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{

    #[Route('/', name: 'app_dashboard')]
    public function index(
        IssueRepository $issueRepository,
    ): Response
    {
        $issues = $issueRepository->findAll();

        // Sort issues by priority number (ASC)
        usort($issues, function($a, $b) {
            $priorityA = $a->getPriority() ? $a->getPriority()->getNumber() : PHP_INT_MAX;
            $priorityB = $b->getPriority() ? $b->getPriority()->getNumber() : PHP_INT_MAX;
            return $priorityA <=> $priorityB;
        });

        return $this->render('dashboard/index.html.twig', [
            'issues' => $issues,
        ]);
    }

    #[Route('/example', name: 'app_example')]
    public function example(): Response
    {
        return $this->render('example.html.twig');
    }

    #[Route('/chat/start', name: 'app_start_chat', methods: ['POST'])]
    public function startChat(
        Request $request,
        EntityManagerInterface $entityManager,
        ProcessingService $processingService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['name']) || !isset($data['email']) || !isset($data['message'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        // Create new issue
        $issue = new Issue();
        $issue->setTitle($data['title']);
        $issue->setSummary('');

        $entityManager->persist($issue);
        $entityManager->flush();

        // Create first message
        $message = new Message();
        $message->setContent($data['message']);
        $message->setIssue($issue);
        $message->setIsCustomer(true);

        $entityManager->persist($message);
        $entityManager->flush();
        $entityManager->refresh($issue);

        $processingService->setPriority($issue);

        return new JsonResponse([
            'success' => true,
            'issueId' => $issue->getId(),
            'messageId' => $message->getId(),
            'message' => $message->getContent()
        ]);
    }

    #[Route('/chat/{issueId}/messages', name: 'app_get_chat_messages', methods: ['GET'])]
    public function getChatMessages(
        int $issueId,
        IssueRepository $issueRepository,
        MessageRepository $messageRepository
    ): JsonResponse {
        $issue = $issueRepository->find($issueId);

        if (!$issue) {
            return new JsonResponse(['error' => 'Issue not found'], 404);
        }

        $messages = $messageRepository->findBy(['Issue' => $issue], ['id' => 'ASC']);

        $messageData = [];
        foreach ($messages as $message) {
            $messageData[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'isCustomer' => $message->isCustomer(),
                'timestamp' => $message->getId() // Using ID as simple timestamp
            ];
        }

        return new JsonResponse([
            'success' => true,
            'messages' => $messageData
        ]);
    }
}
