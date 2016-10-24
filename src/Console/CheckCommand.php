<?php

namespace Expresso\Console;

use Expresso\Console\Output\OutputWatcher;
use Expresso\Context\ContextBuilder;
use Expresso\Expresso;

use Expresso\Helper\Informer;
use Expresso\Server\Remote;
use Expresso\Worker\Worker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as Option;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class CheckCommand extends Command
{
    /**
     * @var Expresso
     */
    private $expresso;

    /**
     * @param Expresso $expresso
     */
    public function __construct(Expresso $expresso)
    {
        parent::__construct('check');
        $this->setHelp('Check the servers configuration');
        $this->expresso = $expresso;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(Input $input, Output $output)
    {
        $outputWatcher = new OutputWatcher($output);
        $informer = new Informer($outputWatcher);
        $project = $this->expresso->get('project');
        $stage = null;
        if ($input->hasArgument('stage') && $input->getArgument('stage')) {
            $stageName = $input->getArgument('stage');
            $stage = $this->expresso->getStage($stageName);
        } elseif ($stageName = $project['default_stage']) {
            $stage = $this->expresso->getStage($stageName);
        } else {
            throw new RuntimeException('You must provide a stage or define one by default in configuration');
        }

        $servers = array();
        foreach ($stage->getServers() as $serverName => $serverConfig) {
            $server = new Remote($serverConfig);

            $servers[$serverName] = $server;
        }

        $worker = new Worker($project['name'], $servers, $output, $informer, $stage->getName());

        $worker->setWorkingDir($worker->getBaseDir());
        $result = $worker->runOnServers('ls');

        foreach ($result as $serverResult) {
            if ($serverResult->toString()) {
                $output->writeln('<info>✔</info> Check on server <info>' . $serverResult->getServer()->getName() . '</info>');
            } else {
                $output->writeln('<fg=red>✘</fg=red> Check on server <info>' . $serverResult->getServer()->getName() . '</info>');
            }
        }

    }
}
