<?php

namespace Expresso\Task\Deploy\Package;

use Expresso\Task\TaskAbstract;

class UnwantedFile extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Remove all unwanted files in package';

    protected $shelled = false;

    public function execute()
    {
        $unwantedFiles  = $this->expresso->get('project.unwanted_file');
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $tmpPath = Prepare::PACKAGE_TMP_FOLDER . '/' . $releaseName;
        if (!$this->getWorker()->checkPathExistOnLocal($tmpPath)) {
            $this->getInformer()->taskError(true);
            return;
        }

        if (is_array($unwantedFiles)) {
            foreach ($unwantedFiles as $file) {
                if ($this->getWorker()->checkFileExistOnLocal($tmpPath . '/' . $file)) {
                    $this->getWorker()->runOnLocal('cd ' . $tmpPath . ' && rm ' . $file);
                }
            }
        }
        return;
    }
}
