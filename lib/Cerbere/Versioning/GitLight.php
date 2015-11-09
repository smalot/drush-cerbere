<?php

namespace Cerbere\Versioning;

use Cerbere\Model\Config;

/**
 * Class GitLight
 *
 * @package Cerbere\Versioning
 */
class GitLight implements VersioningInterface
{
    const DEFAULT_GIT_PATH = '/usr/bin/git';

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
        return 'git_light';
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
     * @return void
     */
    public function prepare($config)
    {
        $this->config = $config;
        $this->workDirectory = drush_tempdir();
    }

    /**
     * @param string|null $destination
     * @return string
     */
    public function process($destination = null)
    {
        $command = $this->buildCommandLine($destination);
        drush_print('$> ' . $command);
        passthru($command);
    }

    /**
     * @param string $directory
     *
     * @return string
     */
    protected function buildCommandLine($directory)
    {
        // Git binary path.
        $git = $this->getBinaryPath();

        // Detect git version.
        $return      = exec(escapeshellarg($git) . ' --version');
        $git_version = preg_replace('/[^0-9\.]/', '', $return);

        $command = escapeshellarg($git) . ' clone -q ' . escapeshellarg($this->config['vcs']['url']) . ' --depth 1 ';

        if (version_compare($git_version, '1.7.10', '>=')) {
            if (!empty($this->config['vcs']['branch'])) {
                $command .= '--branch=' . escapeshellarg($this->config['vcs']['branch']) . ' --single-branch ';
            }
        } else {
            if (!empty($this->config['vcs']['branch'])) {
                $command .= '--branch=' . escapeshellarg($this->config['vcs']['branch']) . ' ';
            }
        }

        if (!empty($this->config['vcs']['extra_args'])) {
            $command .= $this->config['vcs']['extra_args'];
        }

        $command .= escapeshellarg($directory);

        return $command;
    }

    /**
     * @return string
     */
    protected function getBinaryPath()
    {
        return !empty($this->config['bin']['git']) ? $this->config['bin']['git'] : self::DEFAULT_GIT_PATH;
    }
}
