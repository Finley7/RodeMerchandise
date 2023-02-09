<?php

namespace App\Command;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ImportMemberCsvCommand',
    description: 'Import members from a CSV file',
)]
class ImportMemberCsvCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('csv', InputArgument::OPTIONAL, 'Add the path to the CSV file')
        ;
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvPath = $input->getArgument('csv');

        if (!$csvPath) {
            $io->note('No CSV file argument was passed.');
            return Command::INVALID;
        }

        $csv = Reader::createFromPath($csvPath);
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        foreach ($records as $record) {
            $member = new Member();
            $member->setNumber($record['member_number']);
            $member->setBirthday(new \DateTime($record['birthday']));

            $io->writeln(sprintf('Member %s was imported', $member->getNumber()));
            $this->entityManager->persist($member);
        }

        $this->entityManager->flush();

        $io->success('Members were imported successfully.');
        return Command::SUCCESS;
    }
}
