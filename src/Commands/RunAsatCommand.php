<?php
namespace App\Commands;

use App\AnalysisTool;
use App\Repository;
use App\Result;
use App\Runners\JavaScriptToolRunner;
use App\Runners\JavaToolrunner;
use App\Runners\PythonToolRunner;
use App\Runners\RubyToolRunner;
use App\Runners\ToolRunner;
use App\Warning;
use App\WarningClassification;
use Carbon\Carbon;
use Exception;
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
            ->addOption('commit', null, InputOption::VALUE_REQUIRED, 'The hash of the commit to run the ASATs on');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = Repository::whereFullName($input->getArgument('repository'))->firstOrFail();
        $runner = $this->getRunnerFor($repo);
        $asats = $input->getArgument('tools') ?: $repo->asats->pluck('name');

        $hash = $input->getOption('commit') ?: $this->getCurrentCommit();
        system("git checkout $hash");
        $commitTimestamp = $this->getCurrentCommitDateTime();

        foreach ($asats as $tool) {
            $output->writeln("<comment>Running $tool...</comment>");
            $runner->run($tool);
            $output->writeln('<info>Analysis complete, ' . $runner->numberOfWarnings($tool) . ' violations detected:</info>');
            foreach ($runner->numWarningsPerCategory($tool) as $category => $count) {
                $output->writeln("<comment>$category: $count error(s)</comment>");
            }
        }
        foreach ($runner->results as $tool => $results) {
            $toolId = AnalysisTool::whereName($tool)->pluck('id')->first();
            $result = Result::firstOrCreate(['repository_id' => $repo->id, 'hash' => $hash, 'committed_at' => $commitTimestamp]);
            $result->analysisTools()->sync([$toolId], false);
            $this->saveResults($results, $result);
        }

        // Re-attach HEAD
        system('git checkout ' . $repo->default_branch);

        $output->writeln('<info>Results saved to database!</info>');
    }

    protected function getCurrentCommit()
    {
        exec('git rev-parse HEAD', $output, $exitCode);

        if ($exitCode == 0)
            return $output[0];

        throw new Exception('Could not obtain the current commit hash.');
    }

    protected function getCurrentCommitDateTime()
    {
        exec('git log -1 --pretty=format:%ct', $output, $exitCode);

        if ($exitCode == 0)
            return Carbon::createFromTimestamp($output[0]);

        throw new Exception('Could not obtain the current commit timestamp.');
    }

    protected function saveResults(array $violations, Result $result)
    {
        $classifications = WarningClassification::lists('id', 'name');
        foreach($violations as $violation) {
            $warning = Warning::firstOrNew(array_except($violation, 'classification') + ['result_id' => $result->id]);

            if (!$warning->exists) {
                $warning->classification()->associate($classifications[$violation['classification']]);
                $result->warnings()->save($warning);
            }
        }
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
