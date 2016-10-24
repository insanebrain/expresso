<?php


namespace Expresso\Stage;

class Stage
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $servers;

    /**
     * Stage constructor.
     * @param $name
     * @param array $servers
     */
    public function __construct($name, array $servers)
    {
        $this->name = $name;
        $this->servers =$servers;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }
}
