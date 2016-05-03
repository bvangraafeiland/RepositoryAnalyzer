<?php
namespace App\Commands;

use App\Repository;
use App\Runners\JavaScriptToolRunner;
use App\Runners\JavaToolrunner;
use App\Runners\PythonToolRunner;
use App\Runners\ResultsCollector;
use App\Runners\RubyToolRunner;
use App\Runners\ToolRunner;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

        $this->addArgument('repository', InputArgument::REQUIRED, 'The repository to analyze')
            ->addArgument('tools', InputArgument::IS_ARRAY|InputArgument::OPTIONAL, 'Run only these ASATs')
            ->addOption('skip', null, InputOption::VALUE_REQUIRED, 'Skip this many commits in history after running the ASAT on the previous commit', 0)
            ->addOption('depth', null, InputOption::VALUE_REQUIRED, 'Number of commits to run the tools on', 1);
            //->addOption('commit', null, InputOption::VALUE_REQUIRED, 'The hash of the commit to run the ASATs on');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = Repository::whereFullName($input->getArgument('repository'))->firstOrFail();
        $runner = $this->getRunnerFor($repo);
        $asats = $input->getArgument('tools') ?: $repo->asats->pluck('name')->toArray();
        //$hash = $input->getOption('commit');

        $collector = new ResultsCollector($runner, $output, $asats);
        $collector->runMany($input->getOption('depth'), $input->getOption('skip'));
        $output->writeln('<info>Results saved to database!</info>');
    }

    /**
     * @param Repository $repository
     *
     * @return ToolRunner
     */
    protected function getRunnerFor(Repository $repository)
    {
        switch (strtolower($repository->language)) {
            case 'ruby':
                return new RubyToolRunner($repository);
            break;
            case 'javascript':
                return new JavaScriptToolRunner($repository);
            break;
            case 'java':
                return new JavaToolrunner($repository);
            break;
            case 'python':
                return new PythonToolRunner($repository);
            break;
        }

        throw new InvalidArgumentException('No tool runner is defined for this repository.');
    }
}
