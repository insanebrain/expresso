<?php

namespace Expresso\Task\Symfony\Doctrine;

use Expresso\Task\TaskAbstract;
use Symfony\Component\Console\Exception\RuntimeException;

class Migrate extends TaskAbstract
{
    CONST PARAM = '--env=prod --no-debug --no-interaction';
    /**
     * @var string
     */
    protected $description = 'Migrate database with doctrine migration';

    protected $shelled = false;

    protected $role = 'db';

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $symfonyConsole = $this->expresso->get('project.symfony.console_path');

        if (!$symfonyConsole) {
            throw new RuntimeException('You must set the symfony console path in expresso.yml');
        }

        $param = $this->expresso->get('project.symfony.param', static::PARAM);

        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);
        $results = $this->getWorker()->checkFileExistOnServers($symfonyConsole);

        foreach ($results as $result) {
            if ($result->toBool()) {
                $this->getWorker()->runOnServers(
                    'php ' . $symfonyConsole . ' doctrine:migrations:migrate ' . $param, array($result->getServer())
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
