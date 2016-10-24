<?php

namespace Expresso\Task\Deploy\Permission;

use Expresso\Task\TaskAbstract;

class Shared extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Change folder and file permission in shared';

    protected $shelled = false;

    public function execute()
    {
        $useSudo = '';
        if ($this->expresso->get('project.permission.use_sudo', false)) {
            $useSudo = 'sudo ';
        }

        $userGroup = '';
        if ($this->expresso->get('project.permission.user_group')) {
            $userGroup = ' ' . $this->expresso->get('project.permission.user_group') . ' ';
        }
        $this->getWorker()->setWorkingDir($this->getWorker()->getSharedDir());
        $targets = $this->expresso->get('project.permission.shared_writable', array());

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
