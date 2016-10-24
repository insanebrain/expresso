<?php

namespace Expresso\Task;


use Expresso\Expresso;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Rollback extends TaskAbstract
{

    /**
     * @var string
     */
    protected $description = 'Deploy project';

    /**
     * @var string
     */
    protected $help = '--release select the release to rollback';

    public function configure()
    {
        Expresso::getExpresso()->getConsole()->getUserDefinition()->addOption(
            new InputOption('release', null, InputOption::VALUE_OPTIONAL, 'Select the release to rollback')
        );

        Expresso::getExpresso()->getConsole()->getUserDefinition()->addOption(
            new InputOption('rm-current', null, InputOption::VALUE_OPTIONAL, 'Remove the current release')
        );
    }

    public function execute()
    {
        $releasesList = $this->getReleasesList();
        $this->getWorker()->setWorkingDir($this->getWorker()->getBaseProjectDir());
        $results = $this->getWorker()->runOnServers('readlink current');
        foreach ($results as $result) {
            var_dump(($releasesList[$result->getServer()->getName()]));
            if (count($releasesList[$result->getServer()->getName()]) > 1) {
                $this->getWorker()->setWorkingDir($this->getWorker()->getBaseProjectDir());

                if ($this->getInput()->getOption('release')) {
                    $releaseName = $this->getInput()->getOption('release');
                } else {
                    $releaseName = $releasesList[$result->getServer()->getName()][1];
                }

                $this->getWorker()->runOnServers('rm -rf current',
                    array($result->getServer())
                );

                $this->getWorker()->runOnServers(
                    'ln -sfn ' . $this->getWorker()->getReleasesDir() . $releaseName . '/ current',
                    array($result->getServer())
                );

                $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir());
                if ($this->getInput()->hasOption('rm-current')) {
                    $this->getWorker()->runOnServers('rm -rf ' . $result->toString(), array($result->getServer()));
                }
            } else {
                throw new \RuntimeException('Not enough release on server ' . $result->getServer()->getName());
            }
        }

        return;
    }

    /**
     * @return array
     */
    protected function getReleasesList()
    {
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir());
        $results = $this->getWorker()->runOnServers('find . -maxdepth 1 -mindepth 1 -type d');

        $releases = array();
        foreach ($results as $result) {
            $releasesList = $result->toArray();
            foreach ($releasesList as $key => $item) {
                $item = basename($item);
                $releasesList[$key] = $item;

            }
            rsort($releasesList);
            $releases[$result->getServer()->getName()] = $releasesList;
        }
        return $releases;
    }
}
