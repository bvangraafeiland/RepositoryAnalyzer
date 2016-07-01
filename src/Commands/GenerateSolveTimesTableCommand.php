<?php
namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 24-06-2016
 * Time: 12:22
 */
class GenerateSolveTimesTableCommand extends Command
{
    protected function configure()
    {
        $this->setName('generate:table:solve_times_statistics')->addArgument('result');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resultValue = $input->getArgument('result');
        $raw = array_map('str_getcsv', file(PROJECT_DIR . '/results/pairwise_solve_times_statistics.csv'));
        $processed = [];

        foreach ($raw as $row) {
            $processed[$row[0]][$row[1]] = $this->getCellValue($row, $resultValue);
        }

        // Header
        $columnNames = implode(' & ', array_map(function($category) {
            return preg_replace('/[a-z_]/', '', title_case($category));
        }, array_keys($processed)));
        $output->writeln('\\hline');
        $output->writeln(' & ' . $columnNames . '\\\\');
        $output->writeln('\\hline');

        // Content
        foreach ($processed as $cat1 => $pairedCategories) {
            $rowName = str_replace('_', ' ', title_case($cat1));
            $output->write("$rowName & ");
            $line = implode(' & ', array_slice($pairedCategories, 0, 9));
            $output->writeln("$line \\\\");
        }

        $output->writeln('\\hline');
    }

    /**
     * @param $row
     *
     * @return string
     */
    protected function getCellValue($row, $reportValue)
    {
        $val = $reportValue == 'p' ? round((float)$row[2], 2) : $row[3];
        return ($row[0] != $row[1]) ? $val . ((float)$row[2] < 0.05 ? '' : '\\cellcolor{gray}') : '\\cellcolor{gray}';
    }
}
