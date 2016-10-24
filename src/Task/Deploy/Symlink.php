<?php

namespace Expresso\Task\Deploy;

use Expresso\Task\TaskAbstract;

class Symlink extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Symlink to current';

    protected $shelled = false;

    public function execute()
    {
        $this->getWorker()->setWorkingDir($this->getWorker()->getBaseProjectDir());
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $this->getWorker()->runOnServers('rm -rf current');
        $this->getWorker()->runOnServers(
            ' ln -sfn ' . $this->getWorker()->getReleasesDir() . $releaseName . '/ current'
        );

        return;
    }
}
