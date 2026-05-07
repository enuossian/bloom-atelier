<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-secret-key',
    description: 'Creates the .env.local file and initialises APP_SECRET key',
)]
class GenerateAppLocalSecretKeyCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $envFile = '.env.local';

        if (file_exists($envFile)) {
            $io->error("The {$envFile} file already exists.");

            return Command::FAILURE;
        }

        $keySecret = bin2hex(random_bytes(16));

        file_put_contents($envFile, "APP_SECRET={$keySecret}");

        $io->success('The .env.local file created and APP_SECRET key initialised');

        return Command::SUCCESS;
    }
}
