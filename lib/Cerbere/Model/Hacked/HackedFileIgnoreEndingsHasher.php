<?php

namespace Cerbere\Model\Hacked;

class HackedFileIgnoreEndingsHasher extends HackedFileHasher {
  /**
   * Returns a hash of the given filename.
   *
   * Ignores file line endings.
   */
  function perform_hash($filename) {
    if (!HackedFileGroup::hacked_file_is_binary($filename)) {
      $file = file($filename, FILE_IGNORE_NEW_LINES);
      return sha1(serialize($file));
    }
    else {
      return sha1_file($filename);
    }
  }

  function fetch_lines($filename) {
    return file($filename, FILE_IGNORE_NEW_LINES);
  }
}
