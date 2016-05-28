<?php
namespace App\Commands;

use App\Export\BasicDataExporter;
use App\Export\PullRequestDataExporter;
use App\Export\RepositoryDataExporter;
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
        $this->setName('export:data')->setDescription('Export all data used for statistics')->addArgument('categories', InputArgument::IS_ARRAY, 'Data categories to limit exporting to (pulls, repositories, warnings)', ['pulls', 'repositories', 'warnings']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($input->getArgument('categories') as $category) {
            if ($category == 'pulls')
                (new PullRequestDataExporter)->export();
            if ($category == 'repositories')
                (new RepositoryDataExporter())->export();
            if ($category == 'basic')
                (new BasicDataExporter())->export();
        }
    }
}
