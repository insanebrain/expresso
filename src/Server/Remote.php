<?php


namespace Expresso\Server;


use Expresso\Server\Remote\PhpSecLib;
use Expresso\Server\Configuration;

class Remote extends CommandAbstract
{
    /**
     * @var string
     */
    protected $deployDir;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var PhpSecLib
     */
    protected $host;

    /**
     * Remote constructor.
     * @param Configuration $serverConfig
     */
    public function __construct($serverConfig)
    {
        $this->host = new PhpSecLib($serverConfig);
        $this->host->connect();
        $this->name = $serverConfig->getName();

        if ($deployDir = $serverConfig->getData('deploy_dir')) {
            $this->deployDir = $deployDir;
        }

        if ($roles = $serverConfig->getData('roles')) {
            $this->roles = $roles;
        }
    }

    /**
     * @param string $command
     * @return string
     */
    public function run($command)
    {
        return $this->host->run($command);
    }

    /**
     * @param string $localPath
     * @param string $remotePath
     * @return string
     */
    public function download($localPath, $remotePath)
    {
        return $this->host->download($localPath, $remotePath);
    }

    /**
     * @param string $localPath
     * @param string $remotePath@return string
     *
     */
    public function upload($localPath, $remotePath)
    {
        return $this->host->upload($localPath, $remotePath);
    }

    /**
     * @return string
     */
    public function getDeployDir()
    {
        return $this->deployDir;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
