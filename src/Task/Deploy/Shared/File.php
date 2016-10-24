<?php

namespace Expresso\Task\Deploy\Shared;

use Expresso\Server\Remote;
use Expresso\Task\TaskAbstract;

class File extends TaskAbstract
{
    /**
     * @var string
     */
    protected $description = 'Setup shared file on remote';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = $this->expresso->get('current_deploy.release_name');
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);

        $sharedFile = $this->expresso->get('project.shared.file', array());

        foreach ($sharedFile as $file) {
            $this->getWorker()->setWorkingDir($this->getWorker()->getSharedDir());
            $results = $this->getWorker()->runOnServers(
                'if [ -f $(echo ' . $file . ') ]; then  echo "true"; fi'
            );
            foreach ($results as $result) {
                //file already shared
                if ($result->toBool()) {
                    $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);

                    $command = 'if [ -f $(echo ' . $file . ') ]; then rm -rf ' . $file . '; fi';
                    $command .= ' && mkdir -p ' . dirname($file) . ' && touch ' . $file;
                    $this->getWorker()->runOnServers($command, array($result->getServer()));

                } else {
                    $this->processFileNotShared($result->getServer(), $file, $releaseName);
                }

                $this->getWorker()->setWorkingDir($this->getWorker()->getSharedDir());

                $command = 'ln -nfs ' . $this->getWorker()->getSharedDir() . $file;
                $command .= ' ' . $this->getWorker()->getReleasesDir() . $releaseName . '/' . $file;
                $this->getWorker()->runOnServers($command, array($result->getServer()));
            }
        }

        return;
    }

    /**
     * @param Remote $server
     * @param string $file
     * @param string $releaseName
     */
    protected function processFileNotShared($server, $file, $releaseName)
    {
        $this->getWorker()->setWorkingDir($this->getWorker()->getSharedDir());

        $this->getWorker()->runOnServers('mkdir -p ' . dirname($file), array($server));
        $this->getWorker()->setWorkingDir($this->getWorker()->getReleasesDir() . $releaseName);

        $result = $this->getWorker()->runOnServers(
            'if [ -f $(echo ' . $file . ') ]; then  echo "true"; fi',
            array($server)
        );

        foreach ($result as $serverResult) {
            if ($serverResult->toBool()) {
                $command = 'cp ' . $file . ' ' . $this->getWorker()->getSharedDir() . $file;
                $this->getWorker()->runOnServers($command, array($serverResult->getServer()));
            } else {
                $command = 'touch ' . $this->getWorker()->getSharedDir() . $file;
                $this->getWorker()->runOnServers($command, array($serverResult->getServer()));
            }
        }
    }
}
