<?php

namespace App\Presentation\Command;

use App\Application\DTO\Request\ParseProductRequestDTO;
use App\Domain\Handler\HandlerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:parse',
    description: 'Парсинг страницы товара с Ozon'
)]
class ParseProductCommand extends Command
{
    public function __construct(
        private readonly HandlerInterface $handler
    )
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('sku', InputArgument::REQUIRED, 'sku product');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $io->title('Старт парсинга страницы');

            $parsingResult = $this->handler->handle(
                new ParseProductRequestDTO($input->getArgument('sku'))
            );

            dump($parsingResult->toArray());

            $io->success('Парсинг завершен успешно');
            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $io->error('Произошла ошибка парсинга: ' . $exception->getMessage());
            return Command::FAILURE;
        }
    }
}
