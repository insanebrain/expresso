<?php

namespace Expresso\Task\Deploy\Package;

use Expresso\Task\TaskAbstract;
use Symfony\Component\Console\Exception\RuntimeException;

class Prepare extends TaskAbstract
{
    const PACKAGE_TMP_FOLDER = '/tmp/expressso';
    /**
     * @var string
     */
    protected $description = 'Prepare package';

    protected $shelled = false;

    public function execute()
    {
        $releaseName = time();
        $this->expresso->set('current_deploy.release_name', $releaseName);
        $tmpPath = static::PACKAGE_TMP_FOLDER . '/' . $releaseName;

        if ($this->getWorker()->checkPathExistOnLocal($tmpPath)) {
            $this->getWorker()->runOnLocal('rm -rf ' . $tmpPath);
        }
        $this->getWorker()->runOnLocal('mkdir -p ' . $tmpPath);

        if ($this->expresso->get('project.vcs.git')) {
            $this->preparePackageWithGit($tmpPath);
            return;
        }

        if ($this->expresso->get('project.vcs.directory')) {
            $this->preparePackageWithDirectory($tmpPath);
            return;
        }

        throw new RuntimeException('No method to get source code configured');
    }

    /**
     * Prepare directory package with git
     * @param string $tmpPath
     */
    protected function preparePackageWithGit($tmpPath)
    {
        $repo = null;
        $args = '';
        if ($this->expresso->get('project.vcs.git.repo')) {
            $repo = $this->expresso->get('project.vcs.git.repo');
        } else {
            $this->informer->taskError();
            throw new RuntimeException('You must set a git repository');
        }

        if ($this->getInput()->getOption('branch')) {
            $args = '-b ' . $this->getInput()->getOption('branch');
        } elseif ($this->expresso->get('project.vcs.git.default_branch')) {
            $args = '-b ' . $this->expresso->get('project.vcs.git.default_branch');
        }

        $args .= ' ';
        $this->getWorker()->runOnLocal(
            'cd ' . $tmpPath . ' && git clone ' . $args . $repo . ' .'
        );
    }

    /**
     * Prepare directory package with a specific directory
     * @param string $tmpPath
     */
    protected function preparePackageWithDirectory($tmpPath)
    {
        $directoryToCopy = $this->expresso->get('project.vcs.directory');

        if (!$this->getWorker()->checkPathExistOnLocal($directoryToCopy)) {
            throw new RuntimeException('The directory "' . $directoryToCopy . '" does not exist');
        }

        $this->getWorker()->runOnLocal(
            'cd ' . $tmpPath . ' && cp -r ' . $directoryToCopy . '/* .'
        );
    }
}
