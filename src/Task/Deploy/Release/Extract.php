<?php

namespace Expresso\Task\Deploy\Release;

use Expresso\Task\Deploy\Package\Prepare;
use Expresso\Task\TaskAbstract;

class Extract extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Extract package in release folder';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $packageName = $this->expresso->get('current_deploy.package_name');
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);
        $this->getWorker()->runOnServers('tar zxvf ' . $packageName);
        $this->getWorker()->runOnServers('rm ' . $packageName);
        $this->getWorker()->runOnServers('cp -r ' . $releaseName . '/. .');
        $this->getWorker()->runOnServers('rm -rf ' . $releaseName);

        return;
    }
}
