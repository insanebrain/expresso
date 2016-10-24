<?php


namespace Expresso\Context\Type;


use Dflydev\DotAccessData\Data;

class Symfony2 extends TypeAbstract
{

    public function prepare()
    {
        $basic = new Basic(array());
        $basic->prepare();
        $configuration = new Data($basic->getConfiguration());

        $configuration->set('tasks.list.deploy', '\Expresso\Task\Context\Deploy\Composer');

        $this->configuration = array_merge_recursive($configuration->export(), $this->data);
    }
}
