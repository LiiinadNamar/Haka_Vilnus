<?php

namespace App\Service;

use App\Entity\Issue;
use App\Repository\LabelRepository;
use App\Repository\PriorityRepository;
use Doctrine\ORM\EntityManagerInterface;
class ProcessingService
{
    public function __construct(
        private ApiService $apiService,
        private PriorityRepository $priorityRepository,
        private LabelRepository $labelRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function setPriority(Issue $issue): void
    {
        // Get all available priorities and create lookup array
        $priorities = $this->priorityRepository->findAll();
        $priorityLookup = array_combine(
            array_map(fn($p) => $p->getNumber(), $priorities),
            $priorities
        );

        // Get all available labels and create lookup array
        $labels = $this->labelRepository->findAll();
        $labelLookup = array_combine(
            array_map(fn($l) => $l->getName(), $labels),
            $labels
        );

        // Build structured AI prompt
        $prompt = $this->buildAIPrompt($issue, $priorityLookup, $labelLookup);

        try {
            // Get AI response
            $response = $this->apiService->run($prompt);

            // Validate and process response
            $this->processAIResponse($issue, $response, $priorityLookup, $labelLookup);

            // Save changes
            $this->entityManager->persist($issue);
            $this->entityManager->flush();

        } catch (\Exception $e) {
            // Log error and set default priority if available
            error_log("AI processing failed for issue {$issue->getId()}: " . $e->getMessage());
            $this->setDefaultPriority($issue, $priorityLookup);
        }
    }

    private function buildAIPrompt(Issue $issue, array $priorities, array $labels): string
    {
        $prompt = "You are a customer support AI assistant. Analyze the following support issue and provide " .
            "a JSON response with priority, summary, and labels.\n\n";

        // Issue context
        $prompt .= "## ISSUE CONTEXT\n";
        $prompt .= "Issue Title: {$issue->getTitle()}\n";
        $prompt .= "Issue ID: {$issue->getId()}\n\n";

        // Customer messages
        $prompt .= "## CUSTOMER MESSAGES\n";
        foreach ($issue->getMessages() as $index => $message) {
            if ($message->isCustomer()) {
                $prompt .= "Message " . ($index + 1) . ": {$message->getContent()}\n";
            }
        }
        $prompt .= "\n";

        // Priority rules
        $prompt .= "## PRIORITY RULES\n";
        $prompt .= "Assign priority based on these conditions (lower number = higher priority):\n";
        foreach ($priorities as $priority) {
            $prompt .= "- Priority {$priority->getNumber()}: {$priority->getPrompt()}\n";
        }
        $prompt .= "\n";

        // Label rules
        $prompt .= "## LABEL RULES\n";
        $prompt .= "Apply labels based on these conditions (only if they match):\n";
        foreach ($labels as $label) {
            $prompt .= "- Label '{$label->getName()}': {$label->getPrompt()}\n";
        }
        $prompt .= "\n";

        // Response format
        $prompt .= "## REQUIRED RESPONSE FORMAT\n";
        $prompt .= "Return a JSON object with exactly these fields:\n";
        $prompt .= "{\n";
        $prompt .= "  \"priority\": <number>,\n";
        $prompt .= "  \"summary\": \"<25 character summary>\",\n";
        $prompt .= "  \"labels\": [\"<label1>\", \"<label2>\"]\n";
        $prompt .= "}\n\n";

        $prompt .= "## INSTRUCTIONS\n";
        $prompt .= "1. Analyze the customer's issue and messages\n";
        $prompt .= "2. Assign the most appropriate priority number based on the rules\n";
        $prompt .= "3. Create a concise 25-character summary of the issue\n";
        $prompt .= "4. Apply only relevant labels that match the conditions\n";
        $prompt .= "5. If no labels match, return an empty array for labels\n";
        $prompt .= "6. Respond with valid JSON only, no additional text\n";

        return $prompt;
    }

    private function processAIResponse(Issue $issue, array $response, array $priorityLookup, array $labelLookup): void
    {
        // Validate required fields
        if (!isset($response['priority']) || !isset($response['summary']) || !isset($response['labels'])) {
            throw new \InvalidArgumentException('AI response missing required fields');
        }

        // Set priority
        $priorityNumber = (int) $response['priority'];
        if (!isset($priorityLookup[$priorityNumber])) {
            throw new \InvalidArgumentException("Invalid priority number: {$priorityNumber}");
        }
        $issue->setPriority($priorityLookup[$priorityNumber]);

        // Set summary
        $summary = trim($response['summary']);
        if (strlen($summary) > 25) {
            $summary = substr($summary, 0, 50);
        }
        $issue->setSummary($summary);

        // Set labels
        $issue->unsetLabels();
        if (is_array($response['labels'])) {
            foreach ($response['labels'] as $labelName) {
                $labelName = trim($labelName);
                if (isset($labelLookup[$labelName])) {
                    $issue->addLabel($labelLookup[$labelName]);
                } else {
                    error_log("Unknown label '{$labelName}' returned by AI for issue {$issue->getId()}");
                }
            }
        }
    }

    private function setDefaultPriority(Issue $issue, array $priorityLookup): void
    {
        // Set to lowest priority (highest number) as fallback
        $defaultPriority = max(array_keys($priorityLookup));
        if (isset($priorityLookup[$defaultPriority])) {
            $issue->setPriority($priorityLookup[$defaultPriority]);
            $issue->setSummary('AI processing failed');
            $this->entityManager->persist($issue);
            $this->entityManager->flush();
        }
    }
}
