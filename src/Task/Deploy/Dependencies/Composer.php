<?php

namespace Expresso\Task\Deploy\Dependencies;

use Expresso\Task\TaskAbstract;
use Symfony\Component\Console\Exception\RuntimeException;

class Composer extends TaskAbstract
{
    CONST PARAM = '--no-dev --prefer-dist --optimize-autoloader --no-progress --no-interaction';
    /**
     * @var string
     */
    protected $description = 'Installs dependencies with Composer';

    protected $shelled = true;

    protected $binary = 'composer';

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $param = $this->expresso->get('project.dependencies.composer.param', static::PARAM);
        $binary = $this->expresso->get('project.dependencies.composer.bin_path', $this->binary);
        $envVar = $this->getEnvVar();
        if ($this->expresso->get('current_deploy.started', false)) {
            $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);
        } else {
            $this->getWorker()->setWorkingDir($this->getWorker()->getCurrentDir());
        }
        $results = $this->getWorker()->checkCommandExistOnServers($binary);

        foreach ($results as $result) {
            if ($result->toBool()) {
                $this->getWorker()->runOnServers($envVar . $binary . ' install ' . $param, array($result->getServer()));
            } else {
                throw new RuntimeException('Unable to find composer');
            }
        }

        return;
    }

    protected function getEnvVar()
    {
        if ($this->expresso->get('project.dependencies.composer.env_var')) {
            return 'export ' . $this->expresso->get('project.dependencies.composer.env_var') . ' && ';
        }
        return null;
    }
}
