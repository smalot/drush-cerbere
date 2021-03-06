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

namespace Cerbere\Parser;

use Cerbere\Model\Project;

/**
 * Class Make
 * @package Cerbere\Parser
 */
class Make extends Ini
{
    /**
     * @var string
     */
    protected $core;

    /**
     * @var string
     */
    protected $api;

    /**
     * @var Project[]
     */
    protected $projects;

    /**
     * @var array
     */
    protected $libraries;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'make';
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param string $content
     *
     * @return void
     */
    public function processContent($content)
    {
        $data = $this->parseContent($content);

        // Core attribute is mandatory since Drupal 7.x.
        $data += array('core' => '6.x', 'api' => '', 'projects' => array(), 'libraries' => array());

        $this->core = $data['core'];
        $this->api = $data['api'];
        $this->projects = array();
        $this->libraries = $data['libraries'];

        // Wrap project into objects.
        foreach ($data['projects'] as $project_name => $project_details) {
            $project_details['version'] = $this->getCore() . '-' . $project_details['version'];

            $project = new Project($project_name, $this->getCore(), $project_details['version']);
            $project->setDetails($project_details);

            $this->projects[$project_name] = $project;
        }

        // Todo: wrap libraries into objects.
    }

    /**
     * @return string
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @parser string $filename
     * @return bool
     */
    public function supportedFile($filename)
    {
        return preg_match('/\.make$/i', $filename) > 0;
    }

    /**
     * @return array
     */
    public function getLibraries()
    {
        return $this->libraries;
    }

    /**
     * @param string $project
     *
     * @return Project
     */
    public function getProject($project)
    {
        return $this->projects[$project];
    }

    /**
     * @param string $project
     *
     * @return bool
     */
    public function hasProject($project)
    {
        return isset($this->projects[$project]);
    }
}
