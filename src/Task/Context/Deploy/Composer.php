<?php

namespace Expresso\Task\Context\Deploy;


use Expresso\Expresso;
use Expresso\Task\TaskAbstract;
use Symfony\Component\Console\Input\InputOption;

class Composer extends TaskAbstract
{

    /**
     * @var string
     */
    protected $description = 'Deploy a composer project';

    /**
     * @var string
     */
    protected $help = '--branch or -b for set branch to deploy';

    public function configure()
    {
        Expresso::getExpresso()->getConsole()->getUserDefinition()->addOption(
            new InputOption('branch', 'b', InputOption::VALUE_OPTIONAL, 'Select the branch')
        );
    }

    public function execute()
    {
        $this->expresso->set('current_deploy.started', true);
        $this->callTask('deploy:setup');
        $this->callTask('deploy:package:prepare');
        $this->callTask('deploy:package:unwanted:file');
        $this->callTask('deploy:package:unwanted:folder');
        $this->callTask('deploy:package:compress');
        $this->callTask('deploy:release:create');
        $this->callTask('deploy:release:upload');
        $this->callTask('deploy:release:extract');
        $this->callTask('deploy:dependencies:composer');
        $this->callTask('deploy:shared:folder');
        $this->callTask('deploy:shared:file');
        $this->callTask('deploy:permission:release');
        $this->callTask('deploy:permission:shared');
        $this->callTask('deploy:symlink');
        $this->callTask('deploy:clean');

        return;
    }
}
