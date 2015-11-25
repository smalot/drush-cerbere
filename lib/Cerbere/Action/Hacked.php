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
        $files = array(
          'hackedProjectWebFilesDownloader.inc',
          'hackedProjectWebDownloader.inc',
          'hackedProjectWebCSVDownloader.inc',
          'hackedProject.inc',
          'hackedFileIncludeEndingsHasher.inc',
          'hackedFileIgnoreEndingsHasher.inc',
          'hackedFileHasher.inc',
          'hackedFileGroup.inc',
        );

        foreach ($files as $file) {
            require_once __DIR__ . '/../../../modules/hacked/includes/' . $file;
        }
    }

    /**
     * @inheritDoc
     */
    public function process(array $projects, $options = array())
    {
        $project = reset($projects);
        var_dump($project);
    }
}
