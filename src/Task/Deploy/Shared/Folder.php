<?php

namespace Expresso\Task\Deploy\Shared;

use Expresso\Task\TaskAbstract;

class Folder extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Setup shared dir on remote';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);


        $sharedFolder = $this->expresso->get('project.shared.folder', array());

        foreach ($sharedFolder as $folder) {
            $this->getWorker()->setWorkingDir($this->getWorker()->getSharedDir());
            $results = $this->getWorker()->checkPathExistOnServers($folder);
            foreach ($results as $result) {
                //dir already shared
                if ($result->toBool()) {
                    $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);

                    $command = 'if [ -d ' . $folder . ' ]; then rm -rf ' . $folder . '; fi';
                    $this->getWorker()->runOnServers($command, array($result->getServer()));

                } else {
                    $this->getWorker()->setWorkingDir($this->getWorker()->getSharedDir());
                    $this->getWorker()->runOnServers('mkdir -p ' . $folder, array($result->getServer()));
                    $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);
                    $folderExist = $this->getWorker()->checkPathExistOnServers($folder, array($result->getServer()));
                    if ($folderExist[$result->getServer()->getName()]->toBool()) {
                        $command = 'cp -r ' . $folder . '/. ' . $this->getWorker()->getSharedDir() . $folder;
                        $this->getWorker()->runOnServers($command, array($result->getServer()));
                    }
                    $this->getWorker()->runOnServers('rm -rf ' . $folder, array($result->getServer()));
                }

                $this->getWorker()->setWorkingDir($this->getWorker()->getSharedDir());

                $command = 'ln -nfs ' . $this->getWorker()->getSharedDir() . $folder . '/';
                $command .= ' ' . $this->getWorker()->getReleasesDir() . $releaseName . '/' . $folder;
                $this->getWorker()->runOnServers($command, array($result->getServer()));
            }
        }

        return;
    }
}
