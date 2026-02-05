<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-local-secret-key',
    description: 'Creates the .env.dev.local file and initialises the APP_SECRET key.',
)]
class AppGenerateLocalSecretKeyCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $envFile = ".env.dev.local";

        if (file_exists($envFile)) {
            $io->error("The {$envFile} file already exists.");
            return Command::FAILURE;
        }

        $secretKey = bin2hex(random_bytes(16));

        file_put_contents($envFile, "APP_SECRET=$secretKey\n");

        $io->success("The env.dev.local file is created and the APP_SECRET key is initialized.");

        return Command::SUCCESS;
    }
}
