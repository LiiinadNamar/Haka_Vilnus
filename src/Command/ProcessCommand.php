<?php

namespace App\Command;

use App\Repository\IssueRepository;
use App\Service\ProcessingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:process',
    description: 'Add a short description for your command',
)]
class ProcessCommand extends Command
{
    public function __construct(
        private IssueRepository $issueRepository,
        private ProcessingService $priorityService,
    )
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $issues = $this->issueRepository->findAll();

        foreach ($issues as $issue) {
            $this->priorityService->setPriority($issue);
            echo "processed issue: {$issue->getTitle()}\n";
        }

        return Command::SUCCESS;
    }
}
