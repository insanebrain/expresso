<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Expresso\Server;

class Result
{
    /**
     * @var string
     */
    private $output;

    /**
     * @var Remote
     */
    private $server;

    /**
     * @param Remote $server
     * @param string $output
     */
    public function __construct($server, $output)
    {
        $this->server = $server;
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return rtrim($this->output);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return bool
     */
    public function toBool()
    {
        if ('true' === $this->toString()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return explode("\n", $this->toString());
    }
}
