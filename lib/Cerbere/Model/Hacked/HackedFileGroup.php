<?php

namespace Cerbere\Model\Hacked;

/**
 * Represents a group of files on the local filesystem.
 */
class HackedFileGroup {

  var $base_path = '';
  var $files = array();
  var $files_hashes = array();
  var $file_mtimes = array();

  /**
   * @var HackedFileHasher
   */
  var $hasher;

  /**
   * Constructor.
   *
   * @param $base_path
   */
  public function __construct($base_path) {
    $this->base_path = $base_path;
    $this->hasher = new HackedFileIgnoreEndingsHasher();//hacked_get_file_hasher();
  }

  /**
   * Return a new hackedFileGroup listing all files inside the given $path.
   *
   * @param $path
   *
   * @return \Cerbere\Model\Hacked\HackedFileGroup
   */
  static function fromDirectory($path) {
    $filegroup = new self($path);
    // Find all the files in the path, and add them to the file group.
    $filegroup->scan_base_path();
    return $filegroup;
  }

  /**
   * Return a new hackedFileGroup listing all files specified.
   *
   * @param $path
   * @param $files
   *
   * @return \Cerbere\Model\Hacked\HackedFileGroup
   */
  static function fromList($path, $files) {
    $filegroup = new self($path);
    // Find all the files in the path, and add them to the file group.
    $filegroup->files = $files;
    return $filegroup;
  }

  /**
   * Locate all sensible files at the base path of the file group.
   */
  function scan_base_path() {
    $files = self::hacked_file_scan_directory($this->base_path, '/.*/', array(
      '.',
      '..',
      'CVS',
      '.svn',
      '.git'
    ));
    foreach ($files as $file) {
      $filename = str_replace($this->base_path . '/', '', $file->filename);
      $this->files[] = $filename;
    }
  }

  /**
   * Hash all files listed in the file group.
   */
  function compute_hashes() {
    foreach ($this->files as $filename) {
      $this->files_hashes[$filename] = $this->hasher->hash($this->base_path . '/' . $filename);
    }
  }

  /**
   * Determine if the given file is readable.
   * @param string $file
   * @return bool
   */
  function is_readable($file) {
    return is_readable($this->base_path . '/' . $file);
  }

  /**
   * Determine if a file exists.
   * @param string $file
   * @return bool
   */
  function file_exists($file) {
    return file_exists($this->base_path . '/' . $file);
  }

  /**
   * Determine if the given file is binary.
   * @param string $file
   * @return bool
   */
  function is_not_binary($file) {
    return is_readable($this->base_path . '/' . $file) && !self::hacked_file_is_binary($this->base_path . '/' . $file);
  }

  /**
   * @param string $file
   * @return string
   */
  function file_get_location($file) {
    return $this->base_path . '/' . $file;
  }

  /**
   * Determine if a file is a binary file.
   *
   * Taken from: http://www.ultrashock.com/forums/server-side/checking-if-a-file-is-binary-98391.html
   * and then tweaked in: http://drupal.org/node/760362.
   *
   * @param string $file
   *
   * @return bool
   */
  public static function hacked_file_is_binary($file) {
    if (file_exists($file)) {
      if (!is_file($file)) return 0;
      if (!is_readable($file)) return 1;

      $fh  = fopen($file, "r");
      $blk = fread($fh, 512);
      fclose($fh);
      clearstatcache();

      return (
        0 or substr_count($blk, "^\r\n") / 512 > 0.3
        or substr_count($blk, "^ -~") / 512 > 0.3
        or substr_count($blk, "\x00") > 0
      );
    }
    return 0;
  }

  public static function hacked_file_scan_directory($dir, $mask, $nomask = array('.', '..', 'CVS'), $callback = null, $recurse = TRUE, $key = 'filename', $min_depth = 0, $depth = 0) {
    $key = (in_array($key, array('filename', 'basename', 'name')) ? $key : 'filename');
    $files = array();

    if (is_dir($dir) && $handle = opendir($dir)) {
      while (FALSE !== ($file = readdir($handle))) {
        if (!in_array($file, $nomask)) {
          if (is_dir("$dir/$file") && $recurse) {
            // Give priority to files in this folder by merging them in after any subdirectory files.
            $files = array_merge(self::hacked_file_scan_directory("$dir/$file", $mask, $nomask, $callback, $recurse, $key, $min_depth, $depth + 1), $files);
          }
          elseif ($depth >= $min_depth && preg_match($mask, $file)) {
            // Always use this match over anything already set in $files with the same $$key.
            $filename = "$dir/$file";
            $basename = basename($file);
            $name = substr($basename, 0, strrpos($basename, '.'));
            $files[$$key] = new \stdClass();
            $files[$$key]->filename = $filename;
            $files[$$key]->basename = $basename;
            $files[$$key]->name = $name;
            if (is_callable($callback)) {
              $callback($filename);
            }
          }
        }
      }

      closedir($handle);
    }

    return $files;
  }
}
