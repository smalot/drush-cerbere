<?php

namespace Cerbere\Model\Hacked;

/**
 * Represents a group of files on the local filesystem.
 */
class HackedFileGroup
{
    /**
     * @var string
     */
    protected $base_path = '';

    /**
     * @var array
     */
    protected $files = array();

    /**
     * @var array
     */
    protected $files_hashes = array();

    /**
     * @var array
     */
    protected $file_mtimes = array();

    /**
     * @var HackedFileHasher
     */
    protected $hasher;

    /**
     * Constructor.
     *
     * @param string $base_path
     * @param HackedFileHasher $hasher
     */
    public function __construct($base_path, HackedFileHasher $hasher = null)
    {
        if (null === $hasher) {
            $hasher = new HackedFileIgnoreEndingsHasher();
        }

        $this->base_path = $base_path;
        $this->hasher = $hasher;
    }

    /**
     * Hash all files listed in the file group.
     */
    public function computeHashes()
    {
        foreach ($this->files as $filename) {
            $this->files_hashes[$filename] = $this->hasher->hash($this->base_path . DIRECTORY_SEPARATOR . $filename);
        }
    }

    /**
     * Determine if a file exists.
     * @param string $file
     * @return bool
     */
    public function fileExists($file)
    {
        return file_exists($this->base_path . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * Return a new hackedFileGroup listing all files inside the given $path.
     *
     * @param string $path
     *
     * @return HackedFileGroup
     */
    public static function createFromDirectory($path)
    {
        $filegroup = new self($path);
        // Find all the files in the path, and add them to the file group.
        $filegroup->scanBasePath();

        return $filegroup;
    }

    /**
     * Locate all sensible files at the base path of the file group.
     */
    public function scanBasePath()
    {
        $files = self::scanDirectory(
          $this->base_path,
          '/.*/',
          array(
            '.',
            '..',
            'CVS',
            '.svn',
            '.git',
          )
        );

        foreach ($files as $file) {
            $filename = str_replace($this->base_path . DIRECTORY_SEPARATOR, '', $file->filename);
            $this->files[] = $filename;
        }
    }

    /**
     * @param string $dir
     * @param int $mask
     * @param array $nomask
     * @param callable $callback
     * @param bool|true $recurse
     * @param string $key
     * @param int $min_depth
     * @param int $depth
     *
     * @return array
     */
    public static function scanDirectory(
      $dir,
      $mask,
      $nomask = array('.', '..', 'CVS'),
      $callback = null,
      $recurse = true,
      $key = 'filename',
      $min_depth = 0,
      $depth = 0
    ) {
        $key = (in_array($key, array('filename', 'basename', 'name')) ? $key : 'filename');
        $files = array();

        if (is_dir($dir) && $handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, $nomask)) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $file) && $recurse) {
                        // Give priority to files in this folder by merging them in after any subdirectory files.
                        $files = array_merge(
                          self::scanDirectory(
                            $dir . DIRECTORY_SEPARATOR . $file,
                            $mask,
                            $nomask,
                            $callback,
                            $recurse,
                            $key,
                            $min_depth,
                            $depth + 1
                          ),
                          $files
                        );
                    } elseif ($depth >= $min_depth && preg_match($mask, $file)) {
                        // Always use this match over anything already set in $files with the same $$key.
                        $filename = $dir . DIRECTORY_SEPARATOR . $file;
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

    /**
     * Return a new hackedFileGroup listing all files specified.
     *
     * @param string $path
     * @param array $files
     *
     * @return HackedFileGroup
     */
    public static function createFromList($path, array $files)
    {
        $filegroup = new self($path);
        // Find all the files in the path, and add them to the file group.
        $filegroup->files = $files;

        return $filegroup;
    }

    /**
     * @param string $file
     *
     * @return string|bool
     */
    public function getFileHash($file)
    {
        if (isset($this->files_hashes[$file])) {
            return $this->files_hashes[$file];
        }

        return false;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getFileLocation($file)
    {
        return $this->base_path . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Determine if the given file is binary.
     * @param string $file
     * @return bool
     */
    public function isNotBinary($file)
    {
        return is_readable($this->base_path . DIRECTORY_SEPARATOR . $file)
        && !self::isBinary($this->base_path . DIRECTORY_SEPARATOR . $file);
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
    public static function isBinary($file)
    {
        if (file_exists($file)) {
            if (!is_file($file)) {
                return 0;
            }
            if (!is_readable($file)) {
                return 1;
            }

            $fh = fopen($file, "r");
            $blk = fread($fh, 512);
            fclose($fh);
            clearstatcache();

            return (substr_count($blk, "^\r\n") / 512 > 0.3
              || substr_count($blk, "^ -~") / 512 > 0.3
              || substr_count($blk, "\x00") > 0);
        }

        return 0;
    }

    /**
     * Determine if the given file is readable.
     * @param string $file
     * @return bool
     */
    public function isReadable($file)
    {
        return is_readable($this->base_path . DIRECTORY_SEPARATOR . $file);
    }
}
