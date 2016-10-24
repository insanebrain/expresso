<?php


namespace Expresso\Task;

use Expresso\Expresso;
use Expresso\Server\Remote;
use Expresso\Worker\Worker;
use Symfony\Component\Console\Helper\HelperSet;

use Expresso\Stage\Stage;
use Expresso\Console\Output\OutputWatcher;
use Expresso\Helper\Informer;

abstract class TaskAbstract implements TaskInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Expresso
     */
    protected $expresso;

    /**
     * @var Stage
     */
    protected $stage;

    /**
     * @var string
     */
    protected $help;

    /**
     * @var bool
     */
    protected $shelled = true;

    /**
     * @var \Expresso\Helper\Informer
     */
    protected $informer;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @varuse Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var Worker
     */
    protected $worker;

    /**
     * @var HelperSet
     */
    protected $helperSet;

    /**
     * @var string
     */
    protected $role = null;

    public function __construct($name)
    {
        if (!$name) {
            throw new \InvalidArgumentException('Task name can not be null');
        }
        $this->name = $name;
    }

    public function init($output, $input, $expresso, $stage, $helperSet)
    {
        $this->output = $output;
        $outputWatcher = new OutputWatcher($output);
        $this->informer = new Informer($outputWatcher);
        $this->input = $input;
        $this->expresso = $expresso;
        $this->setStage($stage);
        $this->helperSet = $helperSet;
    }

    /**
     * Run task
     */
    public function run()
    {
        $this->getInformer()->startTask($this->getName());
        $this->beforeTask();
        $this->execute();
        $this->afterTask();
        $this->getInformer()->endTask();

    }

    /**
     * Execute task
     *
     * @return mixed
     */
    abstract protected function execute();

    /**
     * Call specific task before
     */
    protected function beforeTask()
    {
        foreach ($this->expresso->getTasksBefore($this->getName()) as $taskName) {
            $this->callTask($taskName);
        }
    }

    /**
     * Call specific task after
     */
    protected function afterTask()
    {
        foreach ($this->expresso->getTasksAfter($this->getName()) as $taskName) {
            $this->callTask($taskName);
        }
    }

    public function callTask($name)
    {
        $task = $this->expresso->getTask($name);
        $task->init($this->getOutput(), $this->getInput(), $this->expresso, $this->getStage(), $this->helperSet);
        $task->run();
    }

    /**
     * @param Stage
     * @TODO can be optimize
     */
    protected function setStage($stage)
    {
        $this->stage = $stage;
        $servers = array();
        foreach ($this->stage->getServers() as $serverName => $serverConfig) {
            $server = new Remote($serverConfig);
            if (!$this->role || in_array($this->role, $server->getRoles())) {
                $servers[$serverName] = $server;
            }
        }
        $project = $this->expresso->get('project');
        $this->worker = new Worker(
            $project['name'],
            $servers,
            $this->getOutput(),
            $this->getInformer(),
            $this->stage->getName()
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return boolean
     */
    public function isShelled()
    {
        return $this->shelled;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @return Stage
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * @return \Expresso\Helper\Informer
     */
    public function getInformer()
    {
        return $this->informer;
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Gets a helper instance by name.
     *
     * @param string $name The helper name
     *
     * @return mixed The helper value
     *
     * @throws \LogicException           if no HelperSet is defined
     */
    public function getHelper($name)
    {
        if (null === $this->helperSet) {
            throw new \LogicException(sprintf('Cannot retrieve helper "%s" because there is no HelperSet defined. Did you forget to add your command to the application or to set the application on the command using the setApplication() method? You can also set the HelperSet directly using the setHelperSet() method.',
                $name));
        }

        return $this->helperSet->get($name);
    }


    /**
     * @return Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * Add your own arguments and options
     */
    public function configure()
    {
    }
}
