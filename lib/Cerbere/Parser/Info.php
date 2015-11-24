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
 * Class Info
 * @package Cerbere\Parser
 */
class Info extends Ini
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var Project
     */
    protected $project;

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
        return 'info';
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return array($this->getProject());
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param string $content
     */
    public function processContent($content)
    {
        $data = $this->parseContent($content);
        $data += array('project' => basename($this->filename, '.info'), 'version' => '');

        $project = new Project($data['project'], $data['core'], $data['version']);
        $project->setDetails($data);

        $this->project = $project;
    }

    /**
     * @param string $filename
     */
    public function processFile($filename)
    {
        // Store filename to extract project name.
        $this->filename = $filename;

        parent::processFile($filename);
    }

    /**
     * @parser string $filename
     * @return integer
     */
    public function supportedFile($filename)
    {
        return preg_match('/\.info$/i', $filename) > 0;
    }
}
