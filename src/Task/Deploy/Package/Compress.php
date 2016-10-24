<?php

namespace Expresso\Task\Deploy\Package;

use Expresso\Task\TaskAbstract;

class Compress extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Compress package tmp directory';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $projectName  = $this->expresso->get('project.name');
        $tmpPath = Prepare::PACKAGE_TMP_FOLDER;
        $packageName = $projectName . '.tar.gz';
        $this->expresso->set('current_deploy.package_name', $packageName);
        $this->expresso->set('current_deploy.package_path', $tmpPath . '/' . $packageName);
        if (!$this->getWorker()->checkPathExistOnLocal($tmpPath)) {
            $this->getInformer()->taskError(true);
            return;
        }
        $this->getWorker()->runOnLocal('cd ' . $tmpPath . ' && tar zcvf ' . $packageName . ' ' . $releaseName);

        return;
    }
}
