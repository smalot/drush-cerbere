<?php

namespace Cerbere\Versioning;

use Cerbere\Model\Config;

/**
 * Class Svn
 *
 * @package Cerbere\Versioning
 */
class Svn implements VersioningInterface
{
    const SVN_BINARY_PATH = '/usr/bin/svn';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $workDirectory;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'svn';
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
     * @return mixed
     */
    public function prepare($config)
    {
        $this->config = $config;
        $this->workDirectory = drush_tempdir();
    }

    /**
     * @param string|null $directory
     * @return mixed
     */
    public function process($directory = null)
    {
        $command = $this->buildCommandLine($directory);
        drush_print('$> ' . $command);
        passthru($command);
    }

    /**
     * @param string $directory
     * @return string
     */
    protected function buildCommandLine($directory)
    {
        $bin = !empty($this->config['bin']['svn']) ? $this->config['bin']['svn'] : self::SVN_BINARY_PATH;

        $command = escapeshellarg($bin) . ' checkout ' . escapeshellarg($this->config['url']) . ' ';
        $command.= escapeshellarg($directory) . ' ';

        if (!empty($this->config['extra_args'])) {
            foreach ($this->config['extra_args'] as $param => $value) {
                if (is_numeric($param)) {
                    $command .= escapeshellarg($value) . ' ';
                } else {
                    $command .= $param . '=' . escapeshellarg($value) . ' ';
                }
            }
        }

        return $command;
    }
}
