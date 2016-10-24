<?php

namespace Expresso\Task\Deploy\Permission;

use Expresso\Task\TaskAbstract;

class Release extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Change folder and file permission in release';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $useSudo = '';
        if ($this->expresso->get('project.permission.use_sudo', false)) {
            $useSudo = 'sudo ';
        }

        $userGroup = '';
        if ($this->expresso->get('project.permission.user_group')) {
            $userGroup = ' ' . $this->expresso->get('project.permission.user_group') . ' ';
        }
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);
        $targets = $this->expresso->get('project.permission.release_writable', array());

        foreach ($targets as $writable) {
            $results = $this->getWorker()->runOnServers(
                'if [ -d ' . $writable . ' ] || [ -f ' . $writable . ' ]; then echo "true"; fi'
            );
            foreach ($results as $result) {
                if ($result->toBool()) {

                    $this->getWorker()->runOnServers(
                        $useSudo . 'chmod -R 775 ' . $writable,
                        array($result->getServer())
                    );

                    if ($userGroup) {
                        $this->getWorker()->runOnServers(
                            $useSudo . 'chown -R ' . $userGroup . ' ' . $writable,
                            array($result->getServer())
                        );
                    }
                }
            }
        }

        return;
    }
}
