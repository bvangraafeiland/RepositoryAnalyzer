<?php
namespace App\Commands;

use App\Checkers\JavaChecker;
use App\Checkers\JavaScriptChecker;
use App\Checkers\PythonChecker;
use App\Checkers\RubyChecker;
use App\Repository;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 21-02-2016
 * Time: 16:03
 */
class ProcessProjectsCommand extends ApiUsingCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('process:projects')->setDescription('Update repositories of the given language with information on ASAT usage')
            ->addArgument('language', InputArgument::REQUIRED, 'Language to filter projects')
            ->addArgument('year', InputArgument::OPTIONAL, 'Only process projects created in this year')
            ->addOption('repository', null, InputOption::VALUE_REQUIRED, 'If provided, only this repository will be checked for ASAT usage');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $language = $input->getArgument('language');
        $constraints = ['language' => $language];
        $message = "Gathering ASAT data for $language projects";

        if ($repoName = $input->getOption('repository')) {
            $constraints['full_name'] = $repoName;
            $message = "Gathering ASAT data for $repoName";
        }

        if ($year = $input->getArgument('year')) {
            $constraints[] = ['created_at', '>=', "$year-01-01"];
            $constraints[] = ['created_at', '<=', "$year-12-31"];
            $message .= " created in $year";
        }

        $output->writeln("<comment>$message</comment>");
        $this->processProjects($constraints);
    }

    protected function processProjects($constraints)
    {
        $projects = Repository::where($constraints)->get();
        $count = count($projects);
        $this->output->writeln("$count projects found");
        $this->output->writeln("<comment>Processing projects...</comment>");

        $bar = progressBar($this->output, $count);

        foreach ($projects as $project) {
            $this->updateProject($project);
            $bar->advance();
        }

        $this->output->writeln("\n<info>Done!</info>");
    }

    protected function updateProject(Repository $project)
    {
        $project->uses_asats = $this->{'check' . ucfirst($project->language) . 'ASATS'}($project);
        $this->checkTravis($project);

        $project->save();
    }

    /**
     * - Configuration file in root
     * - Mentioned in Rakefile
     * - Added in Gemfile - "gem 'rubocop'"
     *
     * @param Repository $project
     *
     * @return bool
     */
    protected function checkRubyASATS(Repository $project)
    {
        return (new RubyChecker($project, $this->github))->check();
    }

    protected function checkPythonASATS(Repository $project)
    {
        return (new PythonChecker($project, $this->github))->check();
    }

    protected function checkJavascriptASATS(Repository $project)
    {
        return (new JavaScriptChecker($project, $this->github))->check();
    }

    protected function checkJavaASATS(Repository $project)
    {
        return (new JavaChecker($project, $this->github))->check();
    }

    protected function checkTravis(Repository $project)
    {
        try {
            $travisDetails = json_decode($this->travis->get('/repos/' . $project->full_name)->getBody(), true);

            $project->uses_travis = $travisDetails['last_build_started_at'] && Carbon::parse($travisDetails['last_build_started_at']) >= $project->pushed_at;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404)
                $project->uses_travis = false;
            else
                throw $e;
        }
    }
}
