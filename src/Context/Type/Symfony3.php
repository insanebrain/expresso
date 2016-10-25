<?php


namespace Expresso\Context\Type;


use Dflydev\DotAccessData\Data;

class Symfony3 extends TypeAbstract
{

    public function prepare()
    {
        $basic = new Symfony2(array());
        $basic->prepare();
        $configuration = new Data($basic->getConfiguration());

        $configuration->set('project.symfony.console_path', 'bin/console');
        $configuration->set('project.shared.folder', array('var/logs', 'var/sessions'));
        $configuration->set('project.permission.release_writable', array('var/cache'));
        $configuration->set('project.permission.shared_writable', array('var/logs', 'var/sessions'));

        $this->configuration = array_merge_recursive($configuration->export(), $this->data);
    }
}
