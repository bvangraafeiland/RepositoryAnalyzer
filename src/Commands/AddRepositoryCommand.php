<?php
namespace App\Commands;

use App\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 26-02-2016
 * Time: 17:28
 */
class AddRepositoryCommand extends Command
{
    use GithubApi;

    protected function configure()
    {
        $this->setName('add:repository')->setDescription('Add a single repository to the database');

        $this->addArgument('repository', InputArgument::REQUIRED, 'Full name of the repository to add');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->github->getRepository($input->getArgument('repository'));
        $model = Repository::addIfNew($repo);

        if (!$model)
            $output->writeln('Repository already exists in database');
        else {
            $output->writeln('<info>Repository added</info>');
        }
    }
}
