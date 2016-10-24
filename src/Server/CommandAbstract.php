<?php


namespace Expresso\Server;


abstract class CommandAbstract
{
    /**
     * @var mixed
     */
    protected $host;

    /**
     * @param string $command
     * @return string
     */
    abstract public function run($command);
}
