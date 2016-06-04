<?php
namespace App\Commands;

use App\Export\WarningCountsExporter;
use App\Repository;
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

/*
  $repositories = Repository::whereIn('full_name', $repoNames)->get();
        foreach ($repositories as $repository) {
            $base = basename($repository->full_name);
            $start = $repository->results()->orderBy('committed_at')->first()->committed_at->toFormattedDateString();
            $end = $repository->results()->orderBy('committed_at', 'desc')->first()->committed_at->toFormattedDateString();
            $numResults = $repository->results()->count();
            echo <<<FIG
\\begin{figure}[H]
\\includegraphics[width=0.5\\textwidth]{img/results-warning_counts/$base.eps}
\\caption{Warning counts of $numResults commits to $repository->full_name ranging from $start to $end}
\\label{fig:warning-counts-$base}
\\end{figure}
Some extra info here.

FIG;
        }
 */
