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
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class Yaml
 * @package Cerbere\Parser
 */
class Yaml extends Ini
{
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
        return 'yaml';
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
     * @param string $filename
     */
    public function processContent($content, $filename = null)
    {
        $data = $this->parseContent($content);
        $data += array('project' => basename($filename, '.info.yml'), 'version' => '');

        $project = new Project($data['project'], $data['core'], $data['version']);
        $project->setDetails($data);

        if (!empty($filename)) {
            $project->setFilename(realpath($filename));
        }

        $this->project = $project;
    }

    /**
     * @param string $content
     * @return array
     * @throws ParseException
     */
    protected function parseContent($content)
    {
        $parsed_info = \Symfony\Component\Yaml\Yaml::parse($content);

        $missing_keys = array_diff($this->getRequiredKeys(), array_keys($parsed_info));

        if (!empty($missing_keys)) {
            throw new ParseException('Missing required keys (' . implode(', ', $missing_keys) . ')');
        }

        return $parsed_info;
    }

    /**
     * @return array
     */
    protected function getRequiredKeys()
    {
        return array('type', 'core', 'name');
    }

    /**
     * @param string $filename
     */
    public function processFile($filename)
    {
        $content = file_get_contents($filename);

        $this->processContent($content, $filename);
    }

    /**
     * @parser string $filename
     * @return integer
     */
    public function supportedFile($filename)
    {
        return preg_match('/\.info\.yml$/i', $filename) > 0;
    }
}

