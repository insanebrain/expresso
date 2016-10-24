<?php


namespace Expresso\Context;


class ContextBuilder
{
    private $contextAvailables = array(
        'basic' => '\Expresso\Context\Type\Basic',
        'composer' => '\Expresso\Context\Type\Composer',
        'symfony2' => '\Expresso\Context\Type\Symfony2',
        'symfony3' => '\Expresso\Context\Type\Symfony3',
    );

    public function build($contextName, $data)
    {
        foreach ($this->contextAvailables as $type => $contextAvailable) {
            if ($type == $contextName){
                /**
                 * @var $context \Expresso\Context\Type\TypeAbstract
                 */
                $context = new $contextAvailable($data);
                $context->prepare();
                $context->build();
            }
        }
    }
}
