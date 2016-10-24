<?php

namespace Expresso\Task\Deploy;

use Expresso\Task\TaskAbstract;

class Setup extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Setup remote to deploy';

    protected $shelled = false;

    public function execute()
    {
        $this->getWorker()->setWorkingDir($this->getWorker()->getBaseDir());
        $folderToCreate = array(
            $this->getWorker()->getReleasesDir(),
            $this->getWorker()->getSharedDir(),
        );

        foreach ($folderToCreate as $folder) {
            $results = $this->getWorker()->checkPathExistOnServers($folder);
            foreach ($results as $result) {
                if (!$result->toBool()) {
                    $this->getWorker()->runOnServers('mkdir -p ' . $folder, array($result->getServer()));
                }
            }
        }

        return;
    }
}
