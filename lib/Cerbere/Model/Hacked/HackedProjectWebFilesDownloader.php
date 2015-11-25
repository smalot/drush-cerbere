<?php

namespace Cerbere\Model\Hacked;

use Cerbere\Archiver\ArchiverTar;
use Cerbere\Archiver\Tar;
use Cerbere\Model\Release;

/**
 * Downloads a project using a standard Drupal method.
 */
class HackedProjectWebFilesDownloader extends HackedProjectWebDownloader {

  function download_link() {
    if (!empty($this->project->project_info['releases'][$this->project->existing_version])) {
      /** @var Release $this_release */
      $this_release = $this->project->project_info['releases'][$this->project->existing_version];
      return $this_release->getDownloadLink();
    }
  }

  function download() {
    $dir = $this->get_destination();
    if (!($release_url = $this->download_link())) {
      return FALSE;
    }

    // If our directory already exists, we can just return the path to this cached version
    if (file_exists($dir) && count(HackedFileGroup::hacked_file_scan_directory($dir, '/.*/', array(
        '.',
        '..',
        'CVS',
        '.svn',
        '.git'
      )))
    ) {
      return $dir;
    }

    // Build the destination folder tree if it doesn't already exists.
    mkdir($dir, 0775, TRUE);

    if (!($local_file = $this->file_get($release_url))) {
      return FALSE;
    }
    try {
      $this->archive_extract($local_file, $dir);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Copies a file from $url to the temporary directory for updates.
   *
   * If the file has already been downloaded, returns the the local path.
   *
   * @param $url
   *   The URL of the file on the server.
   *
   * @return string
   *   Path to local file.
   */
  function file_get($url) {
    $parsed_url = parse_url($url);
    $remote_schemes = array('http', 'https', 'ftp', 'ftps', 'smb', 'nfs');
    if (!in_array($parsed_url['scheme'], $remote_schemes)) {
      // This is a local file, just return the path.
      return drupal_realpath($url);
    }

    // Check the cache and download the file if needed.
    $cache_directory = sys_get_temp_dir() . '/hacked-cache';
    $local = $cache_directory . '/' . basename($parsed_url['path']);

    if (!file_exists($cache_directory)) {
      mkdir($cache_directory);
    }

    // Todo: use guzzle.
    $content = file_get_contents($url);

    if ($content !== false && file_put_contents($local, $content)) {
      return $local;
    }

    return false;
  }

  /**
   * Unpack a downloaded archive file.
   *
   * @param string $file
   *   The filename of the archive you wish to extract.
   * @param string $directory
   *   The directory you wish to extract the archive into.
   * @return \ArchiverInterface
   *   The Archiver object used to extract the archive.
   * @throws \Exception on failure.
   */
  function archive_extract($file, $directory) {
    $archiver = new Tar($file);
    if (!$archiver) {
      throw new \Exception(t('Cannot extract %file, not a valid archive.', array('%file' => $file)));
    }

    // Remove the directory if it exists, otherwise it might contain a mixture of
    // old files mixed with the new files (e.g. in cases where files were removed
    // from a later release).
    $files = $archiver->listContents();

    // Unfortunately, we can only use the directory name for this. :(
    $project = substr($files[0], 0, -1);
    $extract_location = $directory . '/' . $project;

    if (file_exists($extract_location)) {
      file_unmanaged_delete_recursive($extract_location);
    }

    $archiver->extract($directory);
    return $archiver;
  }
}
