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

namespace Cerbere\Action;

use Cerbere\Model\Hacked\HackedProject;
use Cerbere\Model\Project;
use Cerbere\Model\ReleaseHistory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Hacked
 * @package Cerbere\Action
 */
class Hacked implements ActionInterface
{
    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return 'hacked';
    }

    /**
     * @inheritDoc
     */
    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
//        $files = array(
//          'hackedProject.inc',
//          'hackedFileGroup.inc',
//          'hackedProjectWebDownloader.inc',
//          'hackedProjectWebFilesDownloader.inc',
//          'hackedProjectWebCVSDownloader.inc',
//          'hackedFileHasher.inc',
//          'hackedFileIncludeEndingsHasher.inc',
//          'hackedFileIgnoreEndingsHasher.inc',
//        );
//
//        foreach ($files as $file) {
//            require_once __DIR__ . '/../../../modules/hacked/includes/' . $file;
//        }
    }

    /**
     * @inheritDoc
     */
    public function process(array $projects, $options = array())
    {
        if (empty($projects)) {
            return array();
        }

        /** @var Project $project */
        $project = reset($projects);
        $release_history = new ReleaseHistory();
        $release_history->prepare($project);

        if ($filename = $project->getFilename()) {
            $current_dir = getcwd();
            // Change current directory to the module directory.
            chdir(dirname($filename));

            $hacked = new HackedProject($project);
            $result = $hacked->computeReport();

            var_dump($result);
            die('test');

            // Restore current directory.
            chdir($current_dir);
        }

        die('error');
    }
}
