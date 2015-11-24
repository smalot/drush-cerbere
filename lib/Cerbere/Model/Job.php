<?php

/**
 * Drush Cerbere command line tools.
 * Copyright (C) 2015 - Sebastien Malot <sebastien@malot.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Cerbere\Model;

use Cerbere\Action\ActionInterface;
use Cerbere\Versioning\VersioningInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Job
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
     * @var ActionInterface
     */
    protected $action;

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
     * @return ActionInterface
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param ActionInterface $action
     */
    public function setAction($action)
    {
        $this->action = $action;
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
     * @return boolean
     */
    public function isPatternNested()
    {
        return $this->pattern_nested;
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
