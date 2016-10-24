<?php

namespace Expresso\Task\Deploy\Release;

use Expresso\Task\TaskAbstract;

class Create extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Create directory for new release';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir());
        $this->getWorker()->runOnServers('mkdir ' . $releaseName);

        return;
    }
}
