<?php
namespace App\Commands;

use App\Export\AnalyzedRepositoryStatsExporter;
use App\Export\BasicDataExporter;
use App\Export\PullRequestDataExporter;
use App\Export\RepositoryDataExporter;
use App\Export\SolveTimeCombiner;
use App\Export\SolveTimeExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 06-05-2016
 * Time: 15:44
 */
class ExportDataCommand extends Command
{
    protected function configure()
    {
        $this->setName('export:data')->setDescription('Export all data used for statistics')->addArgument('category', InputArgument::REQUIRED, 'Data category to export(pulls, repositories, basic, solve_times)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $category = $input->getArgument('category');

        if ($category == 'pulls')
            (new PullRequestDataExporter)->export();
        if ($category == 'repositories')
            (new RepositoryDataExporter)->export();
        if ($category == 'basic')
            (new BasicDataExporter)->export();
        if ($category == 'solve_times')
            (new SolveTimeExporter)->export();
        if ($category == 'combined_solve_times')
            (new SolveTimeCombiner)->export();
        if ($category == 'analyzed_repositories')
            (new AnalyzedRepositoryStatsExporter)->export();
    }
}
