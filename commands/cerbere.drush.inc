<?php

/**
 * @file
 * Cerbere drush file.
 */

use Cerbere\Action\Update;
use Cerbere\Cerbere;
use Cerbere\Event\CerbereProgressBarListener;
use Cerbere\Model\Job;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;
use Cerbere\Versioning\Local;
use Doctrine\Common\Cache\FilesystemCache;

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
        'config' => 'The config file to use for default setting values.',
        'no-cache' => 'Disable cache.',
        'level' => 'Reporting level (all, security, unsupported, update).',
        'no-progress' => 'Disable progress bar.',
      ),
      'outputformat' => array(
        'default' => 'table',
      ),
      'examples' => array(
        'drush cerbere-check-update "sites/all/modules/*.info" --format=json',
        'drush cerbere-check-update profiles/commerce_kickstart/drupal-core.make --no-cache',
      ),
        // No bootstrap at all.
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    );

    return $items;
}

/**
 * Callback for the drush-demo-command command
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
