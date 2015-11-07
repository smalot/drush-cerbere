<?php

/**
 * @file
 * Cerbere drush file.
 */

// Todo: remove for PROD.
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Cerbere\Action\Update;
use Cerbere\Model\Application;
use Cerbere\Model\Config;
use Cerbere\Model\Project;
use Cerbere\Model\ReleaseHistory;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;

/**
 * Implements hook_drush_command().
 */
function cerbere_drush_command()
{
    $items['cerbere-update'] = array(
      'description' => 'Check updates using make file, info file or scanning folder to discover info files.',
        //'aliases' => array('cdc'),
      'arguments'   => array(
        'source' => 'The source file or folder.',
      ),
      'options'     => array(
        'config'        => 'The config file to use for default setting values.',
        'report-format' => 'Reporting format: console (default), json, html.',
        'no-cache'      => 'Disable cache.',
        'level'         => 'Reporting level (all, security, update)',
      ),
      'examples'    => array(
        'drush cerbere-check-update sites/all/modules/ctools/ctools.info',
        'drush cerbere-check-update profiles/commerce_kickstart/drupal-core.make',
      ),
        // No bootstrap at all.
      'bootstrap'   => DRUSH_BOOTSTRAP_NONE,
    );

    return $items;
}

/**
 * Callback for the drush-demo-command command
 */
function drush_cerbere_update()
{
    $patterns = func_get_args();

    $config = new Config();
    $config->loadFromFile(drush_get_option('config', '.cerbere.yml'));
    $config->override('cache', !drush_get_option('no-cache', false), true);
    $config->override('level', drush_get_option('level'), 'all');

    $application = new Application($config);

    // Parsers.
    $application->registerParser(new Make());
    $application->registerParser(new Info());

    // Load projects using parsers.
    $application->loadProjectsFromPatterns($patterns);

    // Actions.
    $application->registerAction(new Update());

    // Run it !
    $application->process('update');
}

/**
 * @param Project $project
 */
function _drush_cerbere_update_log(Project $project)
{
    $release_history = new ReleaseHistory($project);
    $update = new Update();
    $update->compare($project, $release_history);

    $line = str_pad($release_history->getShortName(), 45, ' ', STR_PAD_RIGHT);
    $line .= str_pad($project->getVersion(), 20, ' ', STR_PAD_RIGHT);
    $line .= str_pad($project->getRecommended(), 20, ' ', STR_PAD_RIGHT);

    if ($project->getStatus() != Update::UPDATE_CURRENT) {
        $line .= Update::getStatusLabel($project->getStatus());
        if ($reason = $project->getReason()) {
            $line .= ' (' . $reason . ')';
        }
    }

    drush_print($line);
}
