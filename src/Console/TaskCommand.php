<?php

namespace Expresso\Console;

use Expresso\Expresso;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as Option;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class TaskCommand extends Command
{
    /**
     * @var Expresso
     */
    private $expresso;

    /**
     * @param string $name
     * @param string $description
     * @param Expresso $expresso
     * @param string|null $help
     */
    public function __construct($name, $description, Expresso $expresso, $help = null)
    {
        parent::__construct($name);
        $this->setDescription($description);
        $this->setHelp($help);
        $this->expresso = $expresso;
    }

    protected function configure()
    {
        $this->addArgument('stage', InputArgument::OPTIONAL, 'The stage that will be used');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(Input $input, Output $output)
    {
        $project = $this->expresso->get('project');
        $taskName = $input->getFirstArgument();
        $task = $this->expresso->getTask($taskName);

        $stage = null;
        if ($input->hasArgument('stage') && $input->getArgument('stage')) {
            $stageName = $input->getArgument('stage');
            $stage = $this->expresso->getStage($stageName);
        } elseif ($stageName = $project['default_stage']) {
            $stage = $this->expresso->getStage($stageName);
        } else {
            throw new RuntimeException('You must provide a stage or define one by default in configuration');
        }


        $task->init($output, $input, $this->expresso, $stage, $this->getHelperSet());
        $task->run();
    }
}
