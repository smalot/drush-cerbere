<?php

namespace Cerbere\Versioning;

use Cerbere\Model\Config;
use GitWrapper\Event\GitLoggerListener;
use GitWrapper\GitWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Git
 *
 * @package Cerbere\Versioning
 */
class Git implements VersioningInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var GitWrapper
     */
    protected $wrapper;

    /**
     * @var string
     */
    protected $workDirectory;

    /**
     * @param GitWrapper|null $wrapper
     */
    public function __construct($wrapper = null)
    {
        if (is_null($wrapper)) {
            $wrapper = new GitWrapper();
        }

        $this->wrapper = $wrapper;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'git';
    }

    /**
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->workDirectory;
    }

    /**
     * @param array $config
     *
     * @return void
     */
    public function prepare($config)
    {
        $this->config = $config;
        $this->workDirectory = drush_tempdir();

        if (!empty($this->config['log'])) {
            // Log to a file named "git.log"
            $log = new Logger('cerbere_git');
            $log->pushHandler(new StreamHandler($this->config['log'], Logger::DEBUG));

            // Instantiate the listener, add the logger to it, and register it.
            $listener = new GitLoggerListener($log);
            $this->wrapper->addLoggerListener($listener);
        }
    }

    /**
     * @param string|null $destination
     *
     * @return string
     */
    public function process($destination = null)
    {
        $options = array();

        if (!empty($this->config['branch'])) {
            $options['branch'] = $this->config['branch'];
        }

        if (is_array($this->config['extra_args'])) {
            foreach ($this->config['extra_args'] as $param => $value) {
                if (is_numeric($param)) {
                    $options[$value] = true;
                } else {
                    $options[$param] = $value;
                }
            }
        }

        $this->wrapper->cloneRepository($this->config['url'], $destination, $options);
    }
}
