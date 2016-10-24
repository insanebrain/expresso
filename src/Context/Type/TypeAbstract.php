<?php


namespace Expresso\Context\Type;

use Symfony\Component\Yaml\Yaml;

abstract class TypeAbstract
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $configuration;

    public function __construct($data)
    {
        $this->data = $data;
    }

    abstract public function prepare();

    public function build()
    {
        $yaml = Yaml::dump($this->configuration, 6);

        file_put_contents('expresso.yml', $yaml);
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
