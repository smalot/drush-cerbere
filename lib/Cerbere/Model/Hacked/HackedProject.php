<?php

namespace Cerbere\Model\Hacked;

use Cerbere\Model\Project;

/**
 * Encapsulates a Hacked! project.
 *
 * This class should handle all the complexity for you, and so you should be able to do:
 * <code>
 * $project = hackedProject('context');
 * $project->compute_differences();
 * </code>
 *
 * Which is quite nice I think.
 */
class HackedProject {
  /** @var Project */
  var $project;

  var $name = '';

  var $project_info = array();

  /** @var HackedProjectWebDownloader */
  var $remote_files_downloader;

  /* @var hackedFileGroup $remote_files */
  var $remote_files;

  /* @var hackedFileGroup $local_files */
  var $local_files;

  var $project_type = '';
  var $existing_version = '';

  var $result = array();

  var $project_identified = FALSE;
  var $remote_downloaded = FALSE;
  var $remote_hashed = FALSE;
  var $local_hashed = FALSE;

  /**
   * Constructor.
   * @param Project $propject
   */
  public function __construct(Project $project) {
    $this->project = $project;
    $this->name = $project->getProject();
    $this->remote_files_downloader = new HackedProjectWebFilesDownloader($this);
  }

  /**
   * Get the Human readable title of this project.
   */
  function title() {
    $this->identify_project();
    return isset($this->project_info['title']) ? $this->project_info['title'] : $this->name;
  }

  /**
   * Identify the project from the name we've been created with.
   *
   * We leverage the update (status) module to get the data we require about
   * projects. We just pull the information in, and make descisions about this
   * project being from CVS or not.
   */
  function identify_project() {
    // Only do this once, no matter how many times we're called.
    if (!empty($this->project_identified)) {
      return;
    }

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
    $this->project_info['includes'] = array($this->name . '.module');
  }

  /**
   * Downloads the remote project to be hashed later.
   */
  function download_remote_project() {
    // Only do this once, no matter how many times we're called.
    if (!empty($this->remote_downloaded)) {
      return;
    }

    $this->identify_project();
    $this->remote_downloaded = (bool) $this->remote_files_downloader->download();
  }

  /**
   * Hashes the remote project downloaded earlier.
   */
  function hash_remote_project() {
    // Only do this once, no matter how many times we're called.
    if (!empty($this->remote_hashed)) {
      return;
    }

    // Ensure that the remote project has actually been downloaded.
    $this->download_remote_project();

    // Set up the remote file group.
    $base_path = $this->remote_files_downloader->get_final_destination();
    $this->remote_files = HackedFileGroup::fromDirectory($base_path);
    $this->remote_files->compute_hashes();

    $this->remote_hashed = !empty($this->remote_files->files);

    // Logging.
    if (!$this->remote_hashed) {
      watchdog('hacked', 'Could not hash remote project: @title', array('@title' => $this->title()), WATCHDOG_ERROR);
    }
  }

  /**
   * Locate the base directory of the local project.
   */
  function locate_local_project() {
    // we need a remote project to do this :(
    $this->hash_remote_project();

    // Do we have at least some modules to check for:
    if (!is_array($this->project_info['includes']) || !count($this->project_info['includes'])) {
      return FALSE;
    }

    // If this project is drupal it, we need to handle it specially
    if ($this->project_type != 'core') {
      $includes = array_keys($this->project_info['includes']);
      $include = array_shift($includes);
      $include_type = $this->project_info['project_type'];
    }
    else {
      // Just use the system module to find where we've installed drupal
      $include = 'system';
      $include_type = 'module';
    }

    //$include = 'image_captcha';

    // Todo: check folder.
    $path = getcwd(); //drupal_get_path($include_type, $include);

    // Now we need to find the path of the info file in the downloaded package:
    $temp = '';
    foreach ($this->remote_files->files as $file) {
      if (preg_match('@(^|.*/)' . $include . '.info$@', $file)) {
        $temp = $file;
        break;
      }
    }

    // How many '/' were in that path:
    $slash_count = substr_count($temp, '/');
    $back_track = str_repeat('/..', $slash_count);

    return realpath($path . $back_track);
  }

  /**
   * Hash the local version of the project.
   */
  function hash_local_project() {
    // Only do this once, no matter how many times we're called.
    if (!empty($this->local_hashed)) {
      return;
    }

    $location = $this->locate_local_project();

    $this->local_files = hackedFileGroup::fromList($location, $this->remote_files->files);
    $this->local_files->compute_hashes();

    $this->local_hashed = !empty($this->local_files->files);
  }

  /**
   * Compute the differences between our version and the canonical version of the project.
   */
  function compute_differences() {
    // Make sure we've hashed both remote and local files.
    $this->hash_remote_project();
    $this->hash_local_project();

    $results = array(
      'same' => array(),
      'different' => array(),
      'missing' => array(),
      'access_denied' => array(),
    );

    // Now compare the two file groups.
    foreach ($this->remote_files->files as $file) {
      if ($this->remote_files->files_hashes[$file] == $this->local_files->files_hashes[$file]) {
        $results['same'][] = $file;
      }
      elseif (!$this->local_files->file_exists($file)) {
        $results['missing'][] = $file;
      }
      elseif (!$this->local_files->is_readable($file)) {
        $results['access_denied'][] = $file;
      }
      else {
        $results['different'][] = $file;
      }
    }

    $this->result = $results;
  }

  /**
   * Return a nice report, a simple overview of the status of this project.
   */
  function compute_report() {
    // Ensure we know the differences.
    $this->compute_differences();

    // Do some counting

    $report = array(
      'project_name' => $this->name,
      'status' => HACKED_STATUS_UNCHECKED,
      'counts' => array(
        'same' => count($this->result['same']),
        'different' => count($this->result['different']),
        'missing' => count($this->result['missing']),
        'access_denied' => count($this->result['access_denied']),
      ),
      'title' => $this->title(),
    );

    // Add more details into the report result (if we can).
    $details = array(
      'link',
      'name',
      'existing_version',
      'install_type',
      'datestamp',
      'project_type',
      'includes',
    );
    foreach ($details as $item) {
      if (isset($this->project_info[$item])) {
        $report[$item] = $this->project_info[$item];
      }
    }


    if ($report['counts']['access_denied'] > 0) {
      $report['status'] = HACKED_STATUS_PERMISSION_DENIED;
    }
    elseif ($report['counts']['missing'] > 0) {
      $report['status'] = HACKED_STATUS_HACKED;
    }
    elseif ($report['counts']['different'] > 0) {
      $report['status'] = HACKED_STATUS_HACKED;
    }
    elseif ($report['counts']['same'] > 0) {
      $report['status'] = HACKED_STATUS_UNHACKED;
    }

    return $report;

  }

  /**
   * Return a nice detailed report.
   */
  function compute_details() {
    // Ensure we know the differences.
    $report = $this->compute_report();

    $report['files'] = array();

    // Add extra details about every file.
    $states = array(
      'access_denied' => HACKED_STATUS_PERMISSION_DENIED,
      'missing' => HACKED_STATUS_DELETED,
      'different' => HACKED_STATUS_HACKED,
      'same' => HACKED_STATUS_UNHACKED,
    );

    foreach ($states as $state => $status) {
      foreach ($this->result[$state] as $file) {
        $report['files'][$file] = $status;
        $report['diffable'][$file] = $this->file_is_diffable($file);
      }
    }

    return $report;

  }


  function file_is_diffable($file) {
    $this->hash_remote_project();
    $this->hash_local_project();
    return $this->remote_files->is_not_binary($file) && $this->local_files->is_not_binary($file);
  }

  function file_get_location($storage = 'local', $file) {
    switch ($storage) {
      case 'remote':
        $this->download_remote_project();
        return $this->remote_files->file_get_location($file);
      case 'local':
        $this->hash_local_project();
        return $this->local_files->file_get_location($file);
    }
    return FALSE;
  }


}
