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
 * Class ProjectHackedProxy
 * @package Cerbere\Model
 */
class ProjectHackedProxy extends \hackedProject
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * ProjectHackedProxy constructor.
     *
     * @param \Cerbere\Model\Project $project
     */
    public function __construct(Project $project)
    {
        parent::hackedProject($project->getName());

        $this->project = $project;
    }

    public function identify_project() {
        $data = (array) $this->project;
        $this->project_info = array();
        foreach ($data as $key => $value) {
            $key = str_replace('*', '', $key);
            $this->project_info[$key] = $value;
        }

        $this->project_info['releases'] = $this->project->getReleases();
        $this->project_identified = TRUE;
        $this->existing_version = $this->project->getExistingVersion();
        $this->project_type = 'module';
    }
}
