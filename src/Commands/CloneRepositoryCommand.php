<?php
namespace App\Commands;

use App\Repository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 15-03-2016
 * Time: 15:25
 */
class CloneRepositoryCommand extends Command
{
    protected function configure()
    {
        $this->setName('clone:repo')->addArgument('repo', null, 'Full name of the repository to clone');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repoName = $input->getArgument('repo');
        $repo = Repository::whereFullName($repoName)->firstOrFail();
        $output->writeln('<comment>Cloning repository...</comment>');
        $result = cloneRepository($repo->full_name);
        if (strtolower($repo->language) == 'javascript') {
            $dir = absoluteRepositoriesDir() . "/$repoName";
            system("cd $dir && npm install");
        }
        $message = ($result === 0) ? '<info>Successfully cloned!</info>' : "<error>Something went wrong. Exit code: $result</error>";
        $output->writeln($message);
    }
}
