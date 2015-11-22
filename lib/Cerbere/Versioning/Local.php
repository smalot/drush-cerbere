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
     * @param string $source
     *
     * @return mixed
     */
    public function prepare($source)
    {
        $this->workDirectory = $source;
    }

    /**
     * @param string $source
     * @param string $destination
     * @param array $options
     *
     * @return string
     */
    public function process($source, $destination, $options = array())
    {
        // TODO: Implement process() method.
    }
}
