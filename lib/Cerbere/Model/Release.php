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

/**
 * Class Release
 * @package Cerbere\Model
 */
class Release
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return new \DateTime('@' . $this->getDatestamp());
    }

    /**
     * @return int
     */
    public function getDatestamp()
    {
        return $this->data['date'];
    }

    /**
     * @return string
     */
    public function getDownloadLink()
    {
        return $this->data['download_link'];
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return trim($this->data['files']);
    }

    /**
     * @return int
     */
    public function getFilesize()
    {
        return intval($this->data['filesize']);
    }

    /**
     * @return string
     */
    public function getMDHash()
    {
        return $this->data['mdhash'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * @return string
     */
    public function getProjectStatus()
    {
        return $this->data['project_status'];
    }

    /**
     * @return string
     */
    public function getReleaseLink()
    {
        return $this->data['release_link'];
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->data['status'];
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->data['tag'];
    }

    /**
     * @param $term
     *
     * @return mixed
     */
    public function getTerm($term)
    {
        return $this->data['terms'][$term];
    }

    /**
     * @return array
     */
    public function getTerms()
    {
        return $this->data['terms'];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->data['version'];
    }

    /**
     * @return string
     */
    public function getVersionExtra()
    {
        return isset($this->data['version_extra']) ? $this->data['version_extra'] : '';
    }

    /**
     * @return string
     */
    public function getVersionMajor()
    {
        return $this->data['version_major'];
    }

    /**
     * @return string
     */
    public function getVersionPatch()
    {
        return isset($this->data['version_patch']) ? $this->data['version_patch'] : '';
    }

    /**
     * @param $term
     *
     * @return bool
     */
    public function hasTerm($term)
    {
        return isset($this->data['terms'][$term]);
    }

    /**
     * @param int $project_status
     */
    public function setProjectStatus($project_status)
    {
        $this->data['project_status'] = $project_status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->data['status'] = $status;
    }
}
