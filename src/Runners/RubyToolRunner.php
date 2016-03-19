<?php
namespace App\Runners;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 15:26
 */
class RubyToolRunner extends ToolRunner
{
    protected function runRubocop()
    {
        // Can't pass formatter options to Rake task
        exec("bundle exec rubocop --format json", $output, $exitCode);

        return json_decode($output[0], true);
    }

    /**
     * Retrieve the name of the task that runs RuboCop from the Rakefile
     * @return string
     */
    protected function getRuboCopTask()
    {
        $rakefile = file_get_contents($this->projectDir . '/Rakefile');
        if (preg_match('%RuboCop::RakeTask\.new\(:(.+)\)%', $rakefile, $matches))
            return $matches[1];

        return 'rubocop';
    }

    protected function installDependenciesCommand()
    {
        return 'bundle install';
    }
}
