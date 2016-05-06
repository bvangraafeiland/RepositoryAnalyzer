<?php
namespace App\Commands;

use App\Export\PullRequestDataExporter;
use Symfony\Component\Console\Command\Command;
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
        $this->setName('export:data')->setDescription('Export all data used for statistics');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pulls = new PullRequestDataExporter;
        $pulls->pullRequestsData();
        $pulls->pullRequestCounts();
    }
}
