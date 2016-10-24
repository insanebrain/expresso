<?php

namespace Expresso\Console;

use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Application extends Console
{
    /**
     * Input definition for user specific arguments and options.
     *
     * @var InputDefinition
     */
    private $userDefinition;

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $inputDefinition->addOption(
            new InputOption('--file', '-f', InputOption::VALUE_OPTIONAL, 'Specify expresso.yml file.')
        );

        return $inputDefinition;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return parent::getDefaultCommands();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        return parent::getDefaultHelperSet();
    }

    /**
     * @return InputDefinition
     */
    public function getUserDefinition()
    {
        if (null === $this->userDefinition) {
            $this->userDefinition = new InputDefinition();
        }

        return $this->userDefinition;
    }

    /**
     * Add user definition arguments and options to definition.
     */
    public function addUserArgumentsAndOptions()
    {
        $this->getDefinition()->addArguments($this->getUserDefinition()->getArguments());
        $this->getDefinition()->addOptions($this->getUserDefinition()->getOptions());
    }
}
