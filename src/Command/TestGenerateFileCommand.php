<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DocumentGenerator\GenerateEquipmentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'test:generate', description: 'тестовая генерация файла')]
final class TestGenerateFileCommand extends Command
{
    public function __construct(
        private GenerateEquipmentService $generateEquipmentService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generateEquipmentService->generate();
        return Command::SUCCESS;
    }
}
