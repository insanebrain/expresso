<?php

namespace Expresso\Task\Symfony\Cache;

use Expresso\Task\TaskAbstract;
use Symfony\Component\Console\Exception\RuntimeException;

class Clear extends TaskAbstract
{
    CONST PARAM = '--env=prod --no-debug --no-interaction';
    /**
     * @var string
     */
    protected $description = 'Clear cache';

    protected $shelled = true;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $symfonyConsole = $this->expresso->get('project.symfony.console_path');

        if (!$symfonyConsole) {
            throw new RuntimeException('You must set the symfony console path in expresso.yml');
        }

        $param = $this->expresso->get('project.symfony.param', static::PARAM);

        if ($this->expresso->get('current_deploy.started', false)) {
            $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);
        } else {
            $this->getWorker()->setWorkingDir($this->getWorker()->getCurrentDir());
        }

        $results = $this->getWorker()->checkFileExistOnServers($symfonyConsole);

        foreach ($results as $result) {
            if ($result->toBool()) {
                $this->getWorker()->runOnServers(
                    'php ' . $symfonyConsole . ' cache:clear ' . $param, array($result->getServer())
                );
            } else {
                throw new RuntimeException(
                    'Unable to find symfony console on server ' . $result->getServer()->getName()
                );
            }
        }

        return;
    }
}
