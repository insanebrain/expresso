<?php

namespace Expresso;

use Dflydev\DotAccessData\Data;
use RomaricDrigon\MetaYaml\Exception\NodeValidatorException;
use Symfony\Component\Console;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use RomaricDrigon\MetaYaml\MetaYaml;
use RomaricDrigon\MetaYaml\Loader\YamlLoader;

use Expresso\Console\CheckCommand;
use Expresso\Console\InitCommand;
use Expresso\Server\Configuration;
use Expresso\Stage\Stage;
use Expresso\Task\TaskAbstract;
use Expresso\Console\Application;
use Expresso\Console\TaskCommand;

class Expresso
{
    const KEY_BEFORE = "before";
    const KEY_AFTER = "after";

    /**
     * Global instance of expresso. It's can be accessed only after constructor call.
     * @var Expresso
     */
    private static $instance;

    /**
     * @var Application
     */
    private $console;

    /**
     * @var Console\Input\InputInterface
     */
    private $input;

    /**
     * @var Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \Dflydev\DotAccessData\Data
     */
    private $configuration;


    /**
     * @param Application $console
     * @param Console\Input\InputInterface $input
     * @param Console\Output\OutputInterface $output
     */
    public function __construct(
        Application $console,
        Console\Input\InputInterface $input,
        Console\Output\OutputInterface $output
    )
    {
        $this->console = $console;
        $this->input = $input;
        $this->output = $output;
        $this->configuration = new Data(array());
        static::$instance = $this;
    }

    /**
     * @return Expresso
     */
    public static function getExpresso()
    {
        return static::$instance;
    }

    /**
     * Run console application.
     */
    public function run()
    {
        $this->console->add(new InitCommand($this));
        $this->console->add(new CheckCommand($this));
        $this->addConsoleCommands();
        $this->console->run($this->input, $this->output);
    }

    /**
     * Transform tasks to console commands.
     */
    public function addConsoleCommands()
    {
        foreach ($this->getTasks() as $task) {
            if (!$task->isShelled()) {
                continue;
            }

            $this->console->add(new TaskCommand($task->getName(), $task->getDescription(), $this, $task->getHelp()));
            $task->configure();
        }
        $this->console->addUserArgumentsAndOptions();
    }

    /**
     * Load configuration
     *
     * @param string $filePath
     */
    public function loadConfiguration($filePath)
    {
        $configuration = array();
        if ($filePath) {
            try {
                $loader = new YamlLoader();
                $schema = $loader->loadFromFile(__DIR__ . '/Validator/expresso.validator.yml');
                $schema = new MetaYaml($schema);
                $configuration = Yaml::parse(file_get_contents($filePath));
                $schema->validate($configuration);
            } catch (ParseException $e) {
                $this->taskException(get_class($e), $e->getMessage());
            } catch (NodeValidatorException $e) {
                $this->taskException(get_class($e), $e->getMessage());
            }
        }
        $this->configuration = new Data($configuration);
    }

    /**
     * @return Console\Input\InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return Application
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * @param $key
     * @param mixed $default
     * @return null|mixed
     */
    public function get($key, $default = null)
    {
        return $this->configuration->get($key, $default);

    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->configuration->set($key, $value);
    }

    /**
     * @param $serverName
     * @return \Expresso\Server\Configuration
     * @throws Console\Exception\RuntimeException
     */
    public function getServer($serverName)
    {
        foreach ($this->get('servers') as $serverKey => $server) {
            if ($serverKey == $serverName) {
                return new Configuration($serverKey, $server);
            }
        }
        throw new Console\Exception\RuntimeException('Unable to find server named ' . $serverName);
    }

    /**
     * @return Stage[]
     */
    public function getStages()
    {
        if (is_array($this->get('stages'))) {
            return $this->get('stages');
        }
        return array();
    }

    /**
     * Get stage with his name
     *
     * @param string $stageName
     * @return Stage
     * @throws Console\Exception\RuntimeException
     */
    public function getStage($stageName)
    {
        foreach ($this->getStages() as $stageKey => $serversName) {
            if ($stageKey == $stageName) {
                $servers = array();
                foreach ($serversName as $serverName) {
                    $server = $this->getServer($serverName);
                    $servers[$server->getName()] = $server;
                }

                if (!$servers) {
                    throw new Console\Exception\RuntimeException('At least one server is needed by stage');
                }

                return new Stage($stageKey, $servers);
            }
        }
        throw new Console\Exception\RuntimeException('Unable to find stage named ' . $stageName);
    }

    /**
     * @return TaskAbstract[]
     */
    public function getTasks()
    {
        $tasks = array();
        if (is_array($this->get('tasks')) && array_key_exists('list', $this->get('tasks'))) {
            foreach ($this->get('tasks.list') as $taskName => $taskClass) {
                $tasks[$taskName] = new $taskClass($taskName);
            }
        }
        return $tasks;
    }

    /**
     * @param string $taskName
     * @return array
     */
    public function getTasksBefore($taskName)
    {
        $tasks = $this->getTasks();
        if (array_key_exists(static::KEY_BEFORE, $tasks)
            && is_array($tasks[static::KEY_BEFORE])
            && array_key_exists($taskName, $tasks[static::KEY_BEFORE])
            && $tasks[static::KEY_BEFORE][$taskName]
        ) {

            return $tasks[static::KEY_BEFORE][$taskName];
        }
        return array();
    }

    /**
     * @param string $taskName
     * @return array
     */
    public function getTasksAfter($taskName)
    {
        $tasks = $this->getTasks();
        if (array_key_exists(static::KEY_AFTER, $tasks)
            && is_array($tasks[static::KEY_AFTER])
            && array_key_exists($taskName, $tasks[static::KEY_AFTER])
            && $tasks[static::KEY_AFTER][$taskName]
        ) {

            return $tasks[static::KEY_AFTER][$taskName];
        }
        return array();
    }

    /**
     * Get task with taskName
     * @param string $taskName
     * @return TaskAbstract
     * @throws Console\Exception\RuntimeException
     */
    public function getTask($taskName)
    {
        $tasks = $this->getTasks();
        if (key_exists($taskName, $tasks)) {
            return $tasks[$taskName];
        }

        throw new Console\Exception\RuntimeException('Unable to find task named ' . $taskName);
    }

    /**
     * @param string $exceptionClass
     * @param string $message
     */
    public function taskException($exceptionClass, $message)
    {
        $message = "    $message    ";
        $this->output->writeln([
            "",
            "<error>Exception [$exceptionClass]</error>",
            "<error>" . str_repeat(' ', strlen($message)) . "</error>",
            "<error>$message</error>",
            "<error>" . str_repeat(' ', strlen($message)) . "</error>",
            ""
        ]);
    }
}
