<?php

namespace App\Command;

use App\Entity\Issue;
use App\Entity\Message;
use App\Repository\PriorityRepository;
use App\Service\ApiService;
use App\Service\ProcessingService;
use DeepSeek\DeepSeekClient;
use DeepSeek\Enums\Models;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{
    public function __construct(
        private ProcessingService $priorityService,
        private ApiService $apiService
    )
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $i = new Issue();
        $i->setTitle('help im stuck at the login page');

        $i->addMessage((new Message())->setIsCustomer(true)
            ->setContent('hey im trying to login but my credentials are wrong'));

        $i->addMessage((new Message())->setIsCustomer(false)
            ->setContent('oh hold on looking into this'));



        $this->priorityService->setPriority($i);


        die;
    }
}
