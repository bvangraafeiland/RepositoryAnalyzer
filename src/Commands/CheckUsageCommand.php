<?php
namespace App\Commands;

use App\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 21-02-2016
 * Time: 19:26
 */
abstract class CheckUsageCommand extends Command
{
    protected $projectProperty;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $language = $input->getArgument('language');
        $projects = Repository::whereLanguage($language)->get();
        $count = count($projects);
        $output->writeln("$count projects found");
        $output->writeln("<comment>Checking for $this->projectProperty usage...</comment>");

        $bar = progressBar($output, $count);

        foreach ($projects as $project) {
            $this->updateProject($project);
            $bar->advance();
        }

        $output->writeln("\n<info>Done!</info>");
    }

    protected function configure()
    {
        $this->addArgument('language', InputArgument::REQUIRED, 'Language to filter projects');
    }

    protected abstract function updateProject(Repository $project);
}
