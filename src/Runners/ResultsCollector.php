<?php
namespace App\Runners;

use App\AnalysisTool;
use App\Repository;
use App\Result;
use App\Warning;
use App\WarningClassification;
use Carbon\Carbon;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-04-2016
 * Time: 10:33
 */
class ResultsCollector
{
    /**
     * @var ToolRunner
     */
    protected $runner;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $asats;

    /**
     * @var Repository
     */
    protected $repository;

    protected $classifications;
    protected $analysisTools;

    public function __construct(ToolRunner $runner, OutputInterface $output, array $asats)
    {
        $this->runner = $runner;
        $this->repository = $runner->getRepository();
        $this->output = $output;
        $this->asats = $asats;

        $this->classifications = WarningClassification::pluck('id', 'name');
        $this->analysisTools = AnalysisTool::pluck('id', 'name');
    }

    protected function getCurrentCommit()
    {
        exec('git rev-parse HEAD', $output, $exitCode);

        if ($exitCode == 0)
            return $output[0];

        throw new Exception('Could not obtain the current commit hash.');
    }

    /**
     * Run ASATs for a range of commits.
     *
     * @param $depth
     * @param string $skipUntil Skip commits up to and including this one.
     */
    public function runMany($depth, $skipUntil = null)
    {
        exec("git log -$depth --first-parent --pretty=format:%H", $commitHashes);

        if ($skipUntil) {
            $commitHashes = array_slice($commitHashes, array_search($skipUntil, $commitHashes) + 1);
        }

        foreach ($commitHashes as $commitHash) {
            $this->runTools($commitHash, false);
        }

        $this->resetHead();
    }

    /**
     * Run the given ASATs with the ToolRunner, and save the results to the database.
     *
     * @param string $hash
     *
     * @param bool $resetHeadAfterward Whether to reset HEAD back to the last commit after running.
     *
     * @throws Exception
     */
    public function runTools($hash = null, $resetHeadAfterward = true)
    {
        $this->hash = $hash ?: $this->getCurrentCommit();
        system("git checkout $this->hash");

        $result = Result::firstOrCreate([
            'repository_id' => $this->repository->id,
            'hash' => $this->hash,
            'committed_at' => $this->getCurrentCommitDateTime()
        ]);
        $asats = array_diff($this->asats, $result->analysisTools()->pluck('name')->toArray());

        foreach ($asats as $tool) {
            $this->output->writeln("<comment>Running $tool...</comment>");
            $this->runner->run($tool);
            $this->output->writeln('<info>Analysis complete, ' . $this->runner->numberOfWarnings($tool) . ' violations detected:</info>');
            foreach ($this->runner->numWarningsPerCategory($tool) as $category => $count) {
                $this->output->writeln("<comment>$category: $count error(s)</comment>");
            }
        }
        $this->collectResults($result);

        if ($resetHeadAfterward) {
            $this->resetHead();
        }
    }

    protected function resetHead()
    {
        // Re-attach HEAD
        system('git checkout ' . $this->repository->default_branch);
    }

    /**
     * Save the ToolRunner's results to the database.
     * @throws Exception
     */
    protected function collectResults(Result $result)
    {
        foreach ($this->runner->results as $tool => $violations) {
            $toolId = $this->analysisTools[$tool];
            $result->analysisTools()->attach($toolId);

            $this->saveWarnings($violations, $result);
        }
        $this->runner->resetData();
    }

    protected function getCurrentCommitDateTime()
    {
        exec('git log -1 --pretty=format:%ct', $output, $exitCode);

        if ($exitCode == 0)
            return Carbon::createFromTimestamp($output[0]);

        throw new Exception('Could not obtain the current commit timestamp.');
    }

    protected function saveWarnings(array $violations, Result $result)
    {
        foreach($violations as $violation) {
            $warning = Warning::firstOrNew(array_except($violation, 'classification') + ['result_id' => $result->id]);

            if (!$warning->exists) {
                $warning->classification()->associate($this->classifications[$violation['classification']]);
                $result->warnings()->save($warning);
            }
        }
    }
}
