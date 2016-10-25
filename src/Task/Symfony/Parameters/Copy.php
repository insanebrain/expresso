<?php

namespace Expresso\Task\Symfony\Parameters;

use Expresso\Server\Remote;
use Expresso\Task\Deploy\Package\Prepare;
use Expresso\Task\TaskAbstract;

class Copy extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Copy parameters.yml.{stage} to parameters.yml';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $tmpPath = Prepare::PACKAGE_TMP_FOLDER . '/' . $releaseName;
        $parametersPath = $tmpPath . '/app/config/parameters.yml.' . $this->getStage()->getName();
        if ($this->getWorker()->checkFileExistOnLocal($parametersPath)) {
            $this->getWorker()->runOnLocal(
                'mv ' . $parametersPath . ' ' . $tmpPath .'/app/config/parameters.yml'
            );
        }
        return;
    }
}
