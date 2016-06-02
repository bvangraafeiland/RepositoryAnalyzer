<?php
namespace App\Commands;

use App\Export\WarningCountsExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 02-06-2016
 * Time: 13:44
 */
class ExportWarningCountsCommand extends Command
{
    protected function configure()
    {
        $this->setName('export:warnings')
            ->setDescription('Export all data used for statistics')->addArgument('repo', InputArgument::OPTIONAL, 'Repository to collect data from');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $providedRepo = $input->getArgument('repo');
        $projects = require PROJECT_DIR . '/config/projects.php';

        $repoNames = $providedRepo ? [$providedRepo] : array_keys(array_flatten($projects, 1));
        foreach ($repoNames as $repoName) {
            if (!file_exists(PROJECT_DIR .  "/results/warning_counts/$repoName.csv")) {
                $output->writeln("Counting for $repoName...");
                $exporter = new WarningCountsExporter($repoName);
                $exporter->export();
            }
        }
    }
}
