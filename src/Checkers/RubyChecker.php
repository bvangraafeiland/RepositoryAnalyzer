<?php
namespace App\Checkers;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 04-03-2016
 * Time: 14:30
 */
class RubyChecker extends ProjectChecker
{
    public function doLanguageSpecificProcessing()
    {
        $hasConfigFile = (bool) $this->project->getFile('.rubocop.yml');
        $hasDependency = $this->project->fileContains('Gemfile', 'rubocop');
        $hasBuildTask = $this->project->fileContains('Rakefile', 'RuboCop::RakeTask.new') || $this->project->fileContains('Makefile', 'rubocop');

        if (!$hasDependency && $hasBuildTask) {
            // Check for gemspec file, project may be a ruby gem
            $projectRootFiles = array_pluck($this->github->getContent($this->project->full_name), 'name');
            $gemspec = $this->findGemspecFile($projectRootFiles);
            $hasDependency = $gemspec && $this->project->fileContains($gemspec, 'rubocop');
        }

        return $this->attachASAT('rubocop', $hasConfigFile, $hasDependency, $hasBuildTask);
    }

    protected function findGemspecFile(array $filenames)
    {
        $projectName = explode('/', $this->project->full_name)[1];
        $defaultFileNames = ['.gemspec', "$projectName.gemspec"];

        foreach ($defaultFileNames as $name) {
            if (in_array($name, $filenames)) {
                return $name;
            }
        }

        foreach ($filenames as $filename) {
            // Gemspec file may be prefixed with project name but doesn't have to be
            if (preg_match("%(.*).gemspec%", $filename))
                return $filename;
        }
        return null;
    }

    protected function getBuildTools()
    {
        return [
            'rake' => 'Rakefile'
        ];
    }
}
