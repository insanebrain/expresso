<?php


namespace Expresso\Context\Type;


use Dflydev\DotAccessData\Data;

class Symfony2 extends TypeAbstract
{

    public function prepare()
    {
        $basic = new Composer(array());
        $basic->prepare();
        $configuration = new Data($basic->getConfiguration());

        $configuration->set('project.symfony.console_path', 'app/console');
        $configuration->set('project.dependencies.composer.en_var', 'SYMFONY_ENV=prod');

        $configuration->set('tasks.list.symfony:parameters:copy', '\Expresso\Task\Symfony\Parameters\Copy');
        $configuration->set('tasks.list.symfony:doctrine:migrate', '\Expresso\Task\Symfony\Doctrine\Migrate');
        $configuration->set('tasks.list.symfony:cache:clear', '\Expresso\Task\Symfony\Cache\Clear');
        $configuration->set('tasks.list.deploy', '\Expresso\Task\Context\Deploy\Symfony');
        $configuration->set('tasks.after.deploy:dependencies:composer', 'symfony:doctrine:migrate');

        $configuration->set('project.shared.folder', array('app/logs'));
        $configuration->set('project.permission.release_writable', array('app/cache'));
        $configuration->set('project.permission.shared_writable', array('app/logs'));

        $this->configuration = array_merge_recursive($configuration->export(), $this->data);
    }
}
