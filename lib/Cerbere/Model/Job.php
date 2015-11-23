<?php

namespace Cerbere\Model;

use Cerbere\Versioning\VersioningInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Job
 *
 * @package Cerbere\Model
 */
class Job
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var VersioningInterface
     */
    protected $versioning = null;

    /**
     * @var string
     */
    protected $source_url;

    /**
     * @var array
     */
    protected $source_options;

    /**
     * @var array
     */
    protected $patterns = array();

    /**
     * @var boolean
     */
    protected $pattern_nested = false;

    /**
     * @var string
     */
    protected $workingDirectory = '';

    /**
     */
    public function __construct()
    {
    }

    /**
     * @return Job
     */
    public function checkoutRepository()
    {
        $this->getVersioning()->prepare($this->source_url);
        $workingDirectory = $this->getVersioning()->getWorkingDirectory();
        $this->getVersioning()->process($this->source_url, $workingDirectory, $this->source_options);

        return $workingDirectory;
    }

    /**
     * @return VersioningInterface
     */
    public function getVersioning()
    {
        return $this->versioning;
    }

    /**
     * @param VersioningInterface $versioning
     *
     * @return Job
     */
    public function setVersioning(VersioningInterface $versioning)
    {
        $this->versioning = $versioning;

        return $this;
    }

    /**
     * Gets the dispatcher used by this library to dispatch events.
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * Sets the dispatcher used by this library to dispatch events.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The Symfony event dispatcher object.
     *
     * @return $this
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @return boolean
     */
    public function isPatternNested()
    {
        return $this->pattern_nested;
    }

    /**
     * @param array $patterns
     * @param boolean $nested
     *
     * @return Job
     */
    public function setPatterns($patterns, $nested = false)
    {
        $this->patterns = $patterns;
        $this->pattern_nested = $nested;

        return $this;
    }

    /**
     * @param string $url
     * @param array $options
     */
    public function setSource($url, $options = array())
    {
        $this->source_url = $url;
        $this->source_options = $options;
    }
}
