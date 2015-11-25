<?php

namespace Cerbere\Model\Hacked;

/**
 * Base class for downloading remote versions of projects.
 */
class HackedProjectWebDownloader {
  var $project;

  /**
   * Constructor, pass in the project this downloaded is expected to download.
   */
  public function __construct(&$project) {
    $this->project = $project;
  }

  /**
   * Returns a temp directory to work in.
   *
   * @param $namespace
   *   The optional namespace of the temp directory, defaults to the classname.
   */
  function get_temp_directory($namespace = NULL) {
    if (is_null($namespace)) {
      $namespace = get_class($this);
    }
    $segments = array(
      sys_get_temp_dir(),
      'hacked-cache-1',
      $namespace,
    );
    $dir = implode('/', array_filter($segments));
    @mkdir($dir, 0775, TRUE);

    return $dir;
  }

  /**
   * Returns a directory to save the downloaded project into.
   */
  function get_destination() {
    $type = $this->project->project_type;
    $name = $this->project->name;
    $version = $this->project->existing_version;

    $dir = $this->get_temp_directory() . "/$type/$name";
    // Build the destination folder tree if it doesn't already exists.
    @mkdir($dir, 0775, TRUE);

    return "$dir/$version";
  }

  /**
   * Returns the final destination of the unpacked project.
   */
  function get_final_destination() {
    $dir = $this->get_destination();
    $name = $this->project->name;
    $version = $this->project->existing_version;
    $type = $this->project->project_type;
    // More special handling for core:
    if ($type != 'core') {
      $module_dir = $dir . "/$name";
    }
    else {
      $module_dir = $dir . '/' . $name . '-' . $version;
    }

    return $module_dir;
  }

  /**
   * Download the remote files to the local filesystem.
   */
  function download() {

  }

  /**
   * Recursively delete all files and folders in the specified filepath, then
   * delete the containing folder.
   *
   * Note that this only deletes visible files with write permission.
   *
   * @param string $path
   *   A filepath relative to file_directory_path.
   */
  function remove_dir($path) {
    if (is_file($path) || is_link($path)) {
      unlink($path);
    }
    elseif (is_dir($path)) {
      $d = dir($path);
      while (($entry = $d->read()) !== FALSE) {
        if ($entry == '.' || $entry == '..') {
          continue;
        }
        $entry_path = $path . '/' . $entry;
        $this->remove_dir($entry_path);
      }
      $d->close();
      rmdir($path);
    }
    else {

    }
  }

}
