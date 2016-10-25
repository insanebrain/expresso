<?php

namespace Expresso\Task\Deploy;

use Expresso\Task\TaskAbstract;

class Clean extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Clean old releases';

    protected $shelled = false;

    protected $keepRelease = 3;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $useSudo = '';
        if ($this->expresso->get('project.permission.use_sudo', false)) {
            $useSudo = 'sudo ';
        }
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir());
        $keepRelease = $this->expresso->get('project.keep_release', $this->keepRelease);
        $results = $this->getWorker()->runOnServers('find . -maxdepth 1 -mindepth 1 -type d');

        foreach ($results as $result) {
            $releasesList = $result->toArray();
            foreach ($releasesList as $key => $item) {
                $item = basename($item);
                $releasesList[$key] = $item;
                if ($item == $releaseName) {
                    unset($releasesList[$key]);
                }
            }
            rsort($releasesList);
            $releasesList = implode(' ',array_slice($releasesList, $keepRelease - 1));
            if (count($releasesList) > 0) {
                $this->getWorker()->runOnServers($useSudo . ' rm -rf ' . $releasesList);
            }

        }

        return;
    }
}
