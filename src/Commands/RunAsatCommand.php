<?php
namespace App\Commands;

use App\Repository;
use App\Runners\JavaScriptToolRunner;
use App\Runners\JavaToolrunner;
use App\Runners\PythonToolRunner;
use App\Runners\RubyToolRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 18:32
 */
class RunAsatCommand extends Command
{
    protected function configure()
    {
        $this->setName('analyze:repo')->setDescription('Runs ASATs over the given repository');

        $this->addArgument('repository', InputArgument::REQUIRED, 'The repository to analyze');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = Repository::whereFullName($input->getArgument('repository'))->firstOrFail();
        foreach ($repo->asats as $asat) {
            $output->writeln("<comment>Running $asat->name...</comment>");
            $result = $this->{'analyze' . ucfirst($repo->language)}($asat->name, $repo);
            $output->writeln('<info>Analysis complete, ' . $result['summary']['offense_count'] . ' violations detected</info>');
        }
    }

    protected function analyzeRuby($tool, Repository $repo)
    {
        $runner = new RubyToolRunner($repo);
        return $runner->run($tool);
    }

    protected function analyzeJavascript($tool, Repository $repository)
    {
        $runner = new JavaScriptToolRunner($repository);
        return $runner->run($tool);
    }

    protected function analyzePython($tool, Repository $repository)
    {
        $runner = new PythonToolRunner($repository);
        return $runner->run($tool);
    }

    protected function analyzeJava($tool, Repository $repository)
    {
        $runner = new JavaToolrunner($repository);
        return $runner->run($tool);
    }
}
