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

/**
 * @file
 * Cerbere drush file.
 */

use Cerbere\Action\Hacked;
use Cerbere\Action\Update;
use Cerbere\Cerbere;
use Cerbere\Event\CerbereHackedListener;
use Cerbere\Event\CerbereProgressBarListener;
use Cerbere\Model\Job;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;
use Cerbere\Versioning\Local;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * Implementation of hook_drush_help().
 */
function cerbere_drush_help($section) {
    switch ($section) {
        case 'meta:cerbere:title':
            return dt('Cerbere commands');
        case 'meta:cerbere:summary':
            return dt('Examine modules without any database connection.');
    }
}

/**
 * Implements hook_drush_command().
 */
function cerbere_drush_command()
{
    $items['cerbere-update'] = array(
      'description' => 'Check updates using make file, info file or scanning folder to discover info files.',
      'arguments' => array(
        'source' => 'The source file or folder.',
      ),
      'options' => array(
        'no-cache' => 'Disable cache.',
        'level' => 'Reporting level (all, security, unsupported, update).',
        'no-progress' => 'Disable progress bar.',
        'hacked' => 'Append hacked reporting',
      ),
      'outputformat' => array(
        'default' => 'table',
      ),
        // No bootstrap at all.
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    );

    $items['cerbere-hacked'] = array(
      'description' => 'Check if contrib modules has been altered from original.',
      'arguments' => array(
        'source' => 'The source file or folder.',
      ),
      'options' => array(
//        'no-cache' => 'Disable cache.',
//        'level' => 'Reporting level (all, security, unsupported, update).',
        'no-progress' => 'Disable progress bar.',
      ),
      'outputformat' => array(
        'default' => 'table',
      ),
        // No bootstrap at all.
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    );

    return $items;
}

/**
 * Callback for the 'cerbere-update' drush command.
 */
function drush_cerbere_update()
{
    // Get paramaters.
    $patterns = func_get_args();

    // Get options.
    $format    = drush_get_option('format', 'table');
    $flat      = in_array($format, array('table', 'csv'));
    $level     = drush_get_option('level', 'all');
    $use_cache = !drush_get_option('no-cache', false);
    $progress  = !drush_get_option('no-progress', false);
    $hacked    = drush_get_option('hacked', false);

    $cerbere = new Cerbere();

    // Parsers.
    $cerbere->addParser(new Make());
    $cerbere->addParser(new Info());

    // Action.
    $action = new Update();
    if ($use_cache) {
        $cache = new FilesystemCache(sys_get_temp_dir() . '/cerbere');
        $action->setCache($cache);
    }

    // Progress bar.
    if ($progress) {
        $progress_bar = new CerbereProgressBarListener();
        $cerbere->addLoggerListener($progress_bar);
        $action->addLoggerListener($progress_bar);
    }

    // Hacked reporting.
    if ($hacked) {
        $hacked_listener = new CerbereHackedListener();
        $action->addLoggerListener($hacked_listener);
    }

    // Job.
    $job = new Job();
    $job->setVersioning(new Local());
    $job->setAction($action);
    $job->setSource(getcwd(), array());
    $job->setPatterns($patterns, true);

    // Run it !
    $report = $cerbere->run($job, array('flat' => $flat, 'level' => $level));

    return $report;
}

/**
 * Callback for the 'cerbere-hacked' drush command.
 */
function drush_cerbere_hacked()
{
    // Get paramaters.
    $patterns = func_get_args();

    // Get options.
    $format    = drush_get_option('format', 'table');
    $flat      = in_array($format, array('table', 'csv'));
//    $level     = drush_get_option('level', 'all');
//    $use_cache = !drush_get_option('no-cache', false);
    $progress  = !drush_get_option('no-progress', false);

    $cerbere = new Cerbere();

    // Parsers.
    $cerbere->addParser(new Make());
    $cerbere->addParser(new Info());

    // Action.
    $action = new Hacked();
//    if ($use_cache) {
//        $cache = new FilesystemCache(sys_get_temp_dir() . '/cerbere');
//        $action->setCache($cache);
//    }

    // Progress bar.
    if ($progress) {
        $progress_bar = new CerbereProgressBarListener();
        $cerbere->addLoggerListener($progress_bar);
        $action->addLoggerListener($progress_bar);
    }

    // Job.
    $job = new Job();
    $job->setVersioning(new Local());
    $job->setAction($action);
    $job->setSource(getcwd(), array());
    $job->setPatterns($patterns, true);

    // Run it !
    $report = $cerbere->run($job, array('flat' => $flat));

    return $report;
}
