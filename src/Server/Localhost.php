<?php


namespace Expresso\Server;


use Symfony\Component\Process\Process;

class Localhost extends CommandAbstract
{
    const TIMEOUT = 300;
    const NAME_LOCALHOST = 'local';

    /**
     * @param string $command
     * @return string
     * @return string
     */
    public function run($command)
    {
        $process = new Process($command);
        $process
            ->setTimeout(static::TIMEOUT)
            ->setIdleTimeout(static::TIMEOUT)
            ->mustRun();
        return $process->getOutput();
    }

    /**
     * @param string $source
     * @param string $dest
     * @return string
     */
    public function upload($source, $dest)
    {
        return copy($source, $dest);
    }


    /**
     * @param string $source
     * @param string $dest
     * @return string
     */
    public function download($source, $dest)
    {
        copy($dest, $source);
    }
}
