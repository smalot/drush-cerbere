<?php

/**
 * @file
 * Cerbere drush file.
 */

use Cerbere\Action\Update;
use Cerbere\Model\Project;
use Cerbere\Model\ReleaseHistory;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;

/**
 * Implements hook_drush_command().
 */
function cerbere_drush_command() {
  $items['cerbere-update'] = array(
    'description' => 'Check updates using make file, info file or scanning folder to discover info files.',
    //'aliases' => array('cdc'),
    'arguments' => array(
      'source' => 'The source file or folder.',
    ),
    'options' => array(
      'config' => 'The config file to use for default setting values.',
      'report-format' => 'Reporting format: console (default), json, html.',
    ),
    'examples' => array(
      'drush cerbere-check-update sites/all/modules/ctools/ctools.info',
      'drush cerbere-check-update profiles/commerce_kickstart/drupal-core.make',
    ),
    // No bootstrap at all.
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
  );

  return $items;
}

/**
 * Callback for the drush-demo-command command
 */
function drush_cerbere_update() {
  $patterns = func_get_args();

  $application = new \Cerbere\Application();
  $application->loadConfigFilename(drush_get_option('config', '.cerbere.yml'));
  $projects = $application->getProjects($patterns);

  foreach ($projects as $project) {
    _drush_cerbere_update_log($project);
  }
}

/**
 * @param Project $project
 */
function _drush_cerbere_update_log(Project $project) {
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
