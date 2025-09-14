<?php

namespace App\Command;

use App\Entity\Issue;
use App\Entity\Message;
use App\Service\ApiService;
use DeepSeek\DeepSeekClient;
use DeepSeek\Enums\Models;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import',
    description: 'Add a short description for your command',
)]
class ImportCommand extends Command
{
    public function __construct(
        private ApiService $apiService,
        private EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jsonFile = fopen('test.json', 'r');
        if ($jsonFile === false) {
            $output->writeln('Error: Unable to open file');
            return Command::FAILURE;
        }

        $jsonContent = fread($jsonFile, filesize('test.json'));
        fclose($jsonFile);

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln('Error: Invalid JSON format');
            return Command::FAILURE;
        }


        for ($i = 0; $i < 10; $i++) {
            $issueArray = $data[$i];

            $issue = new Issue();
            $issue->setTitle($issueArray['title']);

            foreach ($issueArray['messages'] as $messageArray) {
                $message = new Message();
                $message->setIsCustomer($messageArray['is_customer']);
                $message->setContent($messageArray['content']);
                $message->setIssue($issue);
                $issue->addMessage($message);
                $this->entityManager->persist($message);
            }

            $this->entityManager->persist($issue);

        }

        $this->entityManager->flush();


        return Command::SUCCESS;
    }
}
