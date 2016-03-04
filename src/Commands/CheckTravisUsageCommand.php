<?php
namespace App\Commands;

use App\Repository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 21-02-2016
 * Time: 19:15
 */
class CheckTravisUsageCommand extends CheckUsageCommand
{
    /**
     * @var Client
     */
    protected $travis;

    protected $projectProperty = 'Travis';

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->travis = new Client([
            'base_uri' => 'https://api.travis-ci.org'
        ]);
    }

    protected function configure()
    {
        parent::configure();
        $this->setName('check:travis')->setDescription('Update repositories of the given language with information on recent Travis usage');
    }

    protected function updateProject(Repository $project)
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

        $project->save();
    }
}
