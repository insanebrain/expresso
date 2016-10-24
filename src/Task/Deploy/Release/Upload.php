<?php

namespace Expresso\Task\Deploy\Release;

use Expresso\Task\Deploy\Package\Prepare;
use Expresso\Task\TaskAbstract;

class Upload extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Upload package in release folder';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $packagePath = $this->expresso->get('current_deploy.package_path');
        $packageName = $this->expresso->get('current_deploy.package_name');
        $this->getWorker()->uploadOnServers(
            $packagePath,
            $this->getWorker()->getReleasesDir() . $releaseName . '/' . $packageName
        );

        return;
    }
}
