<?php
namespace App\Commands;

use Psy\Shell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 21-02-2016
 * Time: 17:56
 */
class TinkerCommand extends Command
{
    protected function configure()
    {
        $this->setName('tinker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new Shell)->run($input, $output);
    }
}
