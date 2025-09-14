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

    public function setPriority(Issue $issue)
    {
        $priorities = $this->priorityRepository->findAll();
        $priorities = array_combine(
            array_map(fn($p) => $p->getNumber(), $priorities),
            $priorities
        );

        $prompt = 'json key is "priority" for priority, "summary" for summary, "labels" for labels," ';
        $prompt .= 'summarize the issue in 25 characters, ';
        $prompt .= 'and using these priority conditions give a priority number, ';
        $prompt .= 'conditions: ';
        foreach($priorities as $priority)
        {
            $prompt .= 'priority number is '. $priority->getNumber(). ' if '.$priority->getPrompt() . ';';
        }
        $prompt .= 'help desk messages:  ' ;
        foreach ($issue->getMessages() as $message) {
            if ($message->isCustomer()){
                $prompt .= 'customer -  '  . $message->getContent() . ';';
            } else {
                $prompt .= 'support -   ' .  $message->getContent()  . ';';
            }
        }

        $prompt .= 'support -   ' .  $message->getContent()  . ';';

        // labels
        $labels = $this->labelRepository->findAll();
        $labels = array_combine(
            array_map(fn($l) => $l->getName(), $labels),
            $labels
        );

        $prompt .= 'and using these label conditions add labels, ';
        $prompt .= 'if no label is suits the best, do not add any';
        $prompt .= 'conditions: ';
        foreach ($labels as $label) {
            $prompt .= 'label  is '. $label->getName(). ' if '.$label->getPrompt() . ';';

        }

        $response = $this->apiService->run($prompt);
        $number = $response['priority'];
        $issue->setPriority($priorities[$number]);
        $issue->setSummary($response['summary']);

        $issue->unsetLabels();
        foreach ($response['labels'] as $labelName) {
            if (isset($labels[$labelName])) {
                $issue->addLabel($labels[$labelName]);
            } else {
                // todo warning
            }
        }

        $this->entityManager->persist($issue);
        $this->entityManager->flush();
    }
}
