<?php
namespace App\Commands;

use App\WarningClassification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 31-05-2016
 * Time: 14:04
 */
class UpdateWarningClassificationsCommand extends Command
{
    protected function configure()
    {
        $this->setName('update:warnings')->setDescription('Add missing classifications to warnings');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rules = DB::table('warnings')->distinct()->join('analysis_tools', 'warnings.analysis_tool_id', '=', 'analysis_tools.id')->whereNull('classification_id')->get(['rule AS name', 'analysis_tools.name AS asat_name']);

        $classifications = WarningClassification::pluck('id', 'name');
        $mappings = [];
        foreach (ASATS as $name) {
            $mappings[$name] = require PROJECT_DIR . "/gdc_mappings/$name.php";
        }

        $output->writeln(count($rules) . ' warnings found...');

        foreach ($rules as $rule)  {
            $output->writeln("Updating rule $rule->name");
            $gdcName = $mappings[$rule->asat_name][$rule->name];
            $classification_id = $classifications[$gdcName];
            DB::table('warnings')->whereRule($rule->name)->update(compact('classification_id'));
        };

        $output->writeln('<info>Done!</info>');
    }
}
