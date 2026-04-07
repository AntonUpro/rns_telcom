<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\NotFoundException;
use App\Service\DocumentGenerator\CalculationReportGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'calculation:generate-report',
    description: 'Генерация DOCX-отчёта по расчёту ветровых нагрузок',
)]
final class GenerateCalculationReportCommand extends Command
{
    public function __construct(
        private readonly CalculationReportGenerator $generator,
        private readonly string                     $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'calculationId',
                InputArgument::REQUIRED,
                'ID расчёта',
            )
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Директория для сохранения файла (по умолчанию var/calculation_reports)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $calculationId = (int)$input->getArgument('calculationId');
        if ($calculationId <= 0) {
            $io->error('ID расчёта должен быть положительным целым числом.');
            return Command::FAILURE;
        }

        $outputDir = $input->getOption('output-dir')
            ?? $this->projectDir . '/var/calculation_reports';

        $io->section(sprintf('Генерация отчёта для расчёта #%d', $calculationId));

        try {
            $filePath = $this->generator->generate($calculationId, $outputDir);
        } catch (NotFoundException $e) {
            $io->error(sprintf('Недостаточно данных для генерации: %s', $e->getMessage()));
            return Command::FAILURE;
        } catch (\RuntimeException $e) {
            $io->error(sprintf('Ошибка при сохранении файла: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        $io->success(sprintf('Отчёт успешно сгенерирован: %s', $filePath));

        return Command::SUCCESS;
    }
}
