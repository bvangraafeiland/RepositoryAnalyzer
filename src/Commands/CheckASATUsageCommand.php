<?php
namespace App\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 21-02-2016
 * Time: 16:03
 */
class CheckASATUsageCommand extends CheckUsageCommand
{
    /**
     * @var Client
     */
    protected $githubRawClient;

    protected $projectProperty = 'ASAT';

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->githubRawClient = new Client([
            'base_uri' => 'http://raw.githubusercontent.com',
        ]);
    }

    protected function configure()
    {
        $this->setName('check:asats')->setDescription('Update repositories of the given language with information on ASAT usage')
            ->addArgument('language', InputArgument::REQUIRED, 'Language to filter projects');
    }

    protected function updateProject(Repository $project)
    {
        $project->uses_asats = $this->{'check' . ucfirst($project->language) . 'ASATS'}($project);
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
        return $this->checkASATFile($project, '.rubocop.yml');
    }

    protected function checkPythonASATS(Repository $project)
    {
        return $this->checkASATFile($project, 'pylintrc') || $this->checkASATFile($project, '.pylintrc');
    }

    protected function checkJavascriptASATS(Repository $project)
    {

    }

    /**
     * Check for the existence of the given file in the root directory of the repository
     *
     * @param Repository $project
     * @param $filename
     *
     * @return bool
     */
    protected function checkASATFile(Repository $project, $filename)
    {
        try {
            $this->githubRawClient->get('/' . $project['full_name'] . '/' . $project['default_branch'] . '/' . $filename);
            return true;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return false;
            } else {
                throw $e;
            }
        }
    }
}
