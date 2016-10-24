<?php

namespace Expresso\Console;

use Expresso\Context\ContextBuilder;
use Expresso\Expresso;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as Option;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class InitCommand extends Command
{
    protected $availableProjectType = array(
        'basic' => 'Basic',
        'composer' => 'Composer',
        'symfony2' => 'Symfony2',
        'symfony3' => 'Symfony3',
    );
    protected $vcsAvailables = array('directory' => 'directory', 'git' => 'git');
    /**
     * @var Expresso
     */
    private $expresso;

    /**
     * @param Expresso $expresso
     * @param string|null $help
     */
    public function __construct(Expresso $expresso, $help = null)
    {
        parent::__construct('init');
        $this->setDescription('init project');
        $this->setHelp($help);
        $this->expresso = $expresso;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(Input $input, Output $output)
    {
        $helper = $this->getHelper('question');
        $finder = new Finder();
        $finder->files()->in(getcwd())->depth('== 0')->name('expresso.yml');

        if ($finder->count() > 0) {
            $question = new ConfirmationQuestion(
                'You have already a configuration file, if you continue you will lost this file? [y/n]',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }
        $data = array();
        $question = new Question('Please enter the name of your project : ', null);
        $question->setValidator(function ($name) {
            if (!$name) {
                throw new \RuntimeException(
                    'Project name cannot be empty'
                );
            }
            return $name;
        });
        $data['name'] = $helper->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            'Choose your project type : ',
            $this->availableProjectType,
            0
        );
        $question->setAutocompleterValues($this->availableProjectType);
        $question->setErrorMessage('The choice %s is invalid.');
        $projectType = $helper->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            'Choose VCS : ',
            $this->vcsAvailables,
            0
        );
        $question->setAutocompleterValues($this->vcsAvailables);
        $question->setErrorMessage('The choice %s is invalid.');
        $data['vcs'] = $helper->ask($input, $output, $question);

        if ($data['vcs'] == 'directory') {
            $question = new Question('Please enter the absolute path of your source project : ', null);
            $question->setValidator(function ($path) {
                if (!$path) {
                    throw new \RuntimeException(
                        'Project path cannot be empty'
                    );
                }
                return $path;
            });
            unset($data['vcs']);
            $data['vcs']['directory'] = $helper->ask($input, $output, $question);
        } elseif ($data['vcs'] == 'git') {
            $question = new Question('Please enter the url to your repository : ', null);
            $question->setValidator(function ($name) {
                if (!$name) {
                    throw new \RuntimeException(
                        'Your repository url cannot be empty'
                    );
                }
                return $name;
            });
            $git['repo'] = $helper->ask($input, $output, $question);

            $question = new Question('Please enter the default branch [master] : ', 'master');

            $git['default_branch'] = $helper->ask($input, $output, $question);
            unset($data['vcs']);
            $data['vcs']['git'] = $git;
        }
        $contextBuilder = new ContextBuilder();
        $contextBuilder->build($projectType, array('project' => $data));
    }
}
