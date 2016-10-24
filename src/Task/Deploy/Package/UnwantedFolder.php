<?php

namespace Expresso\Task\Deploy\Package;

use Expresso\Task\TaskAbstract;

class UnwantedFolder extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Remove all unwanted folder in package';

    protected $shelled = false;

    public function execute()
    {
        $unwantedFolder  = $this->expresso->get('project.unwanted_folder');
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $tmpPath = Prepare::PACKAGE_TMP_FOLDER . '/' . $releaseName;
        if (!$this->getWorker()->checkPathExistOnLocal($tmpPath)) {
            $this->getInformer()->taskError(true);
            return;
        }

        if (is_array($unwantedFolder)) {
            foreach ($unwantedFolder as $folder) {
                if ($this->getWorker()->checkPathExistOnLocal($tmpPath . '/' . $folder)) {
                    $this->getWorker()->runOnLocal('cd ' . $tmpPath . ' && rm -rf ' . $folder);
                }
            }
        }

        return;
    }
}
