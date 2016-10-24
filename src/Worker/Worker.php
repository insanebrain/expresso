<?php


namespace Expresso\Worker;


use Expresso\Server\Localhost;
use Expresso\Server\Remote;
use Expresso\Server\Result;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Worker
{
    const CURRENT_DIR = 'current';
    const RELEASES_DIR = 'releases';
    const SHARED_DIR = 'shared';
    const BASE_PROJECT = '{{deploy_dir}}';
    const SERVER_NAME = '{{server_name}}';

    /**
     * @var Remote[]
     */
    protected $servers;

    /**
     * @var \Expresso\Helper\Informer
     */
    protected $informer;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $workingDir = '';

    /**
     * @var string
     */
    protected $projectName;

    /**
     * @var string
     */
    protected $stageName;

    public function __construct($projectName, $servers, $output, $informer, $stageName)
    {
        if (!$projectName) {
            throw new RuntimeException('You must provide a project name');
        }
        $this->projectName = $projectName;
        $this->servers = $servers;
        $this->output = $output;
        $this->informer = $informer;
        $this->stageName = $stageName;
    }

    /**
     * @param string $command
     * @param array $servers
     * @return Result[]
     * @throws \Exception
     */
    public function runOnServers($command, $servers = null)
    {
        if (!$servers) {
            $servers = $this->servers;
        }
        $response = array();
        foreach ($servers as $server) {
            $preparedCommand = $this->prepareCommand($server, $command);
            $this->informer->onServer($server->getName());
            $this->informer->displayVeryVerbose($preparedCommand);

            try {
                $result = $server->run($preparedCommand);
                $response[$server->getName()] = new Result($server, $result);
                if (!$result) {
                    $result = true;
                }
            } catch (\Exception $e) {
                $response[$server->getName()] = new Result($server, $e->getMessage());
                $this->informer->taskException($server->getName(), get_class($e), $e->getMessage());
                throw $e;
            }

            $this->informer->displayDebug($result);
            if (!$result) {
                $this->informer->taskError(true);
            }
            $this->informer->endOnServer($server->getName());
        }

        return $response;
    }

    /**
     * @param string $localPath
     * @param string $remotePath
     * @param array $servers
     * @return Result[]
     * @throws \Exception
     */
    public function uploadOnServers($localPath, $remotePath, $servers = null)
    {
        if (!$servers) {
            $servers = $this->servers;
        }
        $response = array();
        foreach ($servers as $server) {
            $tmpRemotePath = $this->prepareServerPath($server, $remotePath);
            $this->informer->onServer($server->getName());
            $this->informer->displayVeryVerbose('Upload ' . $localPath . ' on ' . $server->getName() . ':' . $tmpRemotePath);

            try {
                $result = $server->upload($localPath, $tmpRemotePath);
                $response[$server->getName()] = new Result($server, $result);
                if (!$result) {
                    $result = true;
                }
            } catch (\Exception $e) {
                $response[$server->getName()] = new Result($server, $e->getMessage());
                $this->informer->taskException($server->getName(), get_class($e), $e->getMessage());
                throw $e;
            }

            $this->informer->displayDebug($result);
            if (!$result) {
                $this->informer->taskError(true);
            }
            $this->informer->endOnServer($server->getName());
        }

        return $response;
    }

    /**
     * @param string $path
     * @param array $servers
     * @return Result[]
     */
    public function checkPathExistOnServers($path, $servers = null)
    {
        return $this->runOnServers("if [ -d " . $path . " ]; then echo 'true'; fi", $servers);
    }

    /**
     * @param string $command
     * @param array $servers
     * @return Result[]
     */
    public function checkCommandExistOnServers($command, $servers = null)
    {
        return $this->runOnServers("if hash $command 2>/dev/null; then echo 'true'; fi", $servers);
    }

    /**
     * @param string $file
     * @param array $servers
     * @return Result[]
     */
    public function checkFileExistOnServers($file, $servers = null)
    {
        return $this->runOnServers("if [ -f " . $file . " ]; then echo 'true'; fi", $servers);
    }

    /**
     * @param Remote $server
     * @param string $command
     * @return string
     */
    protected function prepareCommand($server, $command)
    {
        $command = 'cd ' . $this->getWorkingDir() . ' && ' . $command;
        return $this->prepareServerPath($server, $command);
    }

    /**
     * @param Remote $server
     * @param string $command
     * @return string
     */
    protected function prepareServerPath($server, $command)
    {
        $command = str_replace(static::BASE_PROJECT, $server->getDeployDir(), $command);
        return str_replace(static::SERVER_NAME, $server->getName(), $command);
    }

    /**
     * @param string $command
     * @return string
     * @throws \Exception
     */
    public function runOnLocal($command)
    {
        $local = new Localhost();

        $this->informer->onServer(Localhost::NAME_LOCALHOST);
        $this->informer->displayVeryVerbose($command);

        try {
            $result = $local->run($command);
            if (!$result) {
                $result = true;
            }
        } catch (\Exception $e) {
            $this->informer->taskException(Localhost::NAME_LOCALHOST, get_class($e), $e->getMessage());
            throw $e;
        }

        $this->informer->displayDebug($result);
        $this->informer->endOnServer(Localhost::NAME_LOCALHOST);

        return $result;
    }


    /**
     * @param string $path
     * @return bool
     */
    public function checkPathExistOnLocal($path)
    {
        $response = $this->runOnLocal("if [ -d " . $path . " ]; then echo 'true'; fi");
        if (preg_replace("/[^a-zA-Z0-9]+/", "", $response) == 'true') {
            $response = true;
        } else {
            $response = false;
        }

        return $response;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function checkFileExistOnLocal($file)
    {
        $response = $this->runOnLocal("if [ -f " . $file . " ]; then echo 'true'; fi");
        if (preg_replace("/[^a-zA-Z0-9]+/", "", $response) == 'true') {
            $response = true;
        } else {
            $response = false;
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getWorkingDir()
    {
        return $this->workingDir;
    }

    /**
     * @return string
     */
    public function getStageName()
    {
        return $this->stageName;
    }

    /**
     * @param string $workingDir
     */
    public function setWorkingDir($workingDir)
    {
        $this->workingDir = $workingDir;
    }

    /**
     * @return string
     */
    public function getCurrentDir()
    {
        return static::BASE_PROJECT . '/' . $this->projectName . '/' . static::SERVER_NAME . '/' . static::CURRENT_DIR . '/';
    }

    /**
     * @return string
     */
    public function getReleasesDir()
    {
        return static::BASE_PROJECT . '/' . $this->projectName . '/' . static::SERVER_NAME . '/' . static::RELEASES_DIR . '/';
    }

    /**
     * @return string
     */
    public function getSharedDir()
    {
        return static::BASE_PROJECT . '/' . $this->projectName . '/' . static::SERVER_NAME . '/' . static::SHARED_DIR . '/';
    }

    /**
     * @return string
     */
    public function getBaseProjectDir()
    {
        return static::BASE_PROJECT . '/' . $this->projectName . '/' . static::SERVER_NAME . '/';
    }

    /**
     * @return string
     */
    public function getBaseDir()
    {
        return static::BASE_PROJECT . '/';
    }
}
