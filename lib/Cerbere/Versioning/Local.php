<?php

namespace Cerbere\Versioning;

/**
 * Class Local
 *
 * @package Cerbere\Versioning
 */
class Local implements VersioningInterface
{
    /**
     * @var string
     */
    protected $workDirectory;

    /**
     * @return string
     */
    public function getCode()
    {
        return 'local';
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
     * @return mixed
     */
    public function prepare($config)
    {
        $this->workDirectory = getcwd();
    }

    /**
     * @param string|null $directory
     *
     * @return mixed
     */
    public function process($directory = null)
    {
        // TODO: Implement process() method.
    }
}
