<?php

namespace Cerbere\Versioning;

use GitWrapper\GitWrapper;

/**
 * Class Git
 *
 * @package Cerbere\Versioning
 */
class Git implements VersioningInterface
{
    /**
     * @var GitWrapper
     */
    protected $wrapper;

    /**
     * @var string
     */
    protected $workDirectory;

    /**
     * @param GitWrapper $wrapper
     */
    public function __construct(GitWrapper $wrapper = null)
    {
        if (null === $wrapper) {
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
     * @param string $source
     *
     * @return void
     */
    public function prepare($source)
    {
        $this->workDirectory = drush_tempdir();
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
        $parameters = array();

        $options += array('arguments' => array());

        foreach ($options['arguments'] as $param => $value) {
            if (is_numeric($param)) {
                $parameters[$value] = true;
            } else {
                $parameters[$param] = $value;
            }
        }

        $this->wrapper->cloneRepository($source, $destination, $parameters);
    }

    /**
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * @param GitWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
    }
}
