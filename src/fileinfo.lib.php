<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Class to process files and folders.
 *
 * Here are implemented the functions needed to open, scan, read, create files
 * and folders using the built-in PHP class SplFileInfo. The SplFileInfo class
 * offers a high-level object oriented interface to information for an individual
 * file.
 */
class GddysecFileInfo extends Gddysec
{
    /**
     * Whether the list of files that can be ignored from the filesystem scan will
     * be used to return the directory tree, this should be disabled when scanning a
     * directory without the need to filter the items in the list.
     *
     * @var boolean
     */
    public $ignore_files = true;

    /**
     * Whether the list of folders that can be ignored from the filesystem scan will
     * be used to return the directory tree, this should be disabled when scanning a
     * path without the need to filter the items in the list.
     *
     * @var boolean
     */
    public $ignore_directories = true;

    /**
     * A list of ignored directory paths, these folders will be skipped during the
     * execution of the file system scans, and any sub-directory or files inside
     * these paths will be ignored too.
     *
     * @var array
     */
    private $ignored_directories = array();

    /**
     * Whether the filesystem scanner should run recursively or not.
     *
     * @var boolean
     */
    public $run_recursively = true;

    /**
     * Whether the directory paths must be skipped or not.
     *
     * This is useful to retrieve the full list of resources inside a parent
     * directory, one case where this option can be set as True is when a folder is
     * required to be deleted recursively, considering that by default the folders
     * are ignored and that a folder may be empty some times there could be issues
     * because the deletion will not reach these resources.
     *
     * @var boolean
     */
    public $skip_directories = true;

    /**
     * Retrieve a long text string with signatures of all the files contained
     * in the main and subdirectories of the folder specified, also the filesize
     * and md5sum of that file. Some folders and files will be ignored depending
     * on some rules defined by the developer.
     *
     * @param  string  $directory Parent directory where the filesystem scan will start.
     * @param  boolean $as_array  Whether the result of the operation will be returned as an array or string.
     * @return array              List of files in the main and subdirectories of the folder specified.
     */
    public function get_directory_tree_md5($directory = '', $as_array = false)
    {
        $project_signatures = '';
        $abspath = self::fixPath(ABSPATH);
        $files = $this->get_directory_tree($directory);

        if ($as_array) {
            $project_signatures = array();
        }

        if ($files) {
            sort($files);

            foreach ($files as $filepath) {
                $file_checksum = @md5_file($filepath);
                $filesize = @filesize($filepath);

                if ($as_array) {
                    $basename = str_replace($abspath . '/', '', $filepath);
                    $project_signatures[ $basename ] = array(
                        'filepath' => $filepath,
                        'checksum' => $file_checksum,
                        'filesize' => $filesize,
                        'created_at' => @filectime($filepath),
                        'modified_at' => @filemtime($filepath),
                    );
                } else {
                    $filepath = str_replace($abspath, $abspath . '/', $filepath);
                    $project_signatures .= sprintf(
                        "%s%s%s%s\n",
                        $file_checksum,
                        $filesize,
                        chr(32),
                        $filepath
                    );
                }
            }
        }

        return $project_signatures;
    }

    /**
     * Retrieve a list with all the files contained in the main and subdirectories
     * of the folder specified. Some folders and files will be ignored depending
     * on some rules defined by the developer.
     *
     * @param  string $directory Parent directory where the filesystem scan will start.
     * @return array             List of files in the main and subdirectories of the folder specified.
     */
    public function get_directory_tree($directory = '')
    {
        if (file_exists($directory) && is_dir($directory)) {
            $tree = array();

            $tree = $this->directoryTree($directory);

            if (is_array($tree) && !empty($tree)) {
                sort($tree); /* Sort in alphabetic order */

                return array_map(array('Gddysec', 'fixPath'), $tree);
            }
        }

        return false;
    }

    /**
     * Retrieve a list with all the files contained in the main and subdirectories
     * of the folder specified. Some folders and files will be ignored depending
     * on some rules defined by the developer.
     *
     * @link https://www.php.net/manual/en/class.recursivedirectoryiterator.php
     * @see  RecursiveDirectoryIterator extends FilesystemIterator
     * @see  FilesystemIterator         extends DirectoryIterator
     * @see  DirectoryIterator          extends SplFileInfo
     * @see  SplFileInfo
     *
     * @param  string $directory Parent directory where the filesystem scan will start.
     * @return array             List of files in the main and subdirectories of the folder specified.
     */
    private function directoryTree($directory = '')
    {
        $files = array();
        $filepath = @realpath($directory);
        $objects = array();

        // Exception for directory name must not be empty.
        if ($filepath === false) {
            return $files;
        }

        try {
            if ($this->run_recursively) {
                $flags = FilesystemIterator::KEY_AS_PATHNAME
                    | FilesystemIterator::CURRENT_AS_FILEINFO
                    | FilesystemIterator::SKIP_DOTS
                    | FilesystemIterator::UNIX_PATHS;
                $objects = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($filepath, $flags),
                    RecursiveIteratorIterator::SELF_FIRST,
                    RecursiveIteratorIterator::CATCH_GET_CHILD
                );
            } else {
                $objects = new DirectoryIterator($filepath);
            }
        } catch (RuntimeException $exception) {
            GddysecEvent::report_exception($exception);
        }

        foreach ($objects as $filepath => $fileinfo) {
            $filename = $fileinfo->getFilename();

            if ($this->ignoreFolderPath(null, $filename)
                || (
                    $this->skip_directories === true
                    && $fileinfo->isDir()
                )
            ) {
                continue;
            }

            if ($this->run_recursively) {
                $directory = dirname($filepath);
            } else {
                $directory = $fileinfo->getPath();
                $filepath = $directory . '/' . $filename;
            }

            if ($this->ignoreFolderPath($directory, $filename)
                || $this->ignoreFilePath($filename)
            ) {
                continue;
            }

            $files[] = $filepath;
        }

        return $files;
    }

    /**
     * Skip some specific directories and file paths from the filesystem scan.
     *
     * @param  string  $directory Directory where the scanner is located at the moment.
     * @param  string  $filename  Name of the folder or file being scanned at the moment.
     * @return boolean            Either TRUE or FALSE representing that the scan should ignore this folder or not.
     */
    private function ignoreFolderPath($directory = '', $filename = '')
    {
        // Ignoring current and parent folders.
        if ($filename == '.' || $filename == '..') {
            return true;
        }

        if ($this->ignore_directories) {
            // Ignore directories based on a common regular expression.
            $filepath = @realpath($directory . '/' . $filename);
            $pattern = '/\/wp-content\/(uploads|cache|backup|w3tc)/';

            if (preg_match($pattern, $filepath)) {
                return true;
            }

            // Ignore directories specified by the administrator.
            if (!empty($this->ignored_directories)) {
                foreach ($this->ignored_directories['directories'] as $ignored_dir) {
                    if (strpos($directory, $ignored_dir) !== false
                        || strpos($filepath, $ignored_dir) !== false
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Skip some specific files from the filesystem scan.
     *
     * @param  string  $filename Name of the folder or file being scanned at the moment.
     * @return boolean           Either TRUE or FALSE representing that the scan should ignore this filename or not.
     */
    private function ignoreFilePath($filename = '')
    {
        if (!$this->ignore_files) {
            return false;
        }

        // Ignoring backup files from our clean ups.
        if (strpos($filename, '_sucuribackup.') !== false) {
            return true;
        }

        // Ignore files specified by the administrator.
        if (!empty($this->ignored_directories)) {
            foreach ($this->ignored_directories['directories'] as $ignored_dir) {
                if (strpos($ignored_dir, $filename) !== false) {
                    return true;
                }
            }
        }

        // Any file maching one of these rules WILL NOT be ignored.
        if (( strpos($filename, '.php') !== false) ||
            ( strpos($filename, '.htm') !== false) ||
            ( strpos($filename, '.js') !== false) ||
            ( strcmp($filename, '.htaccess') == 0     ) ||
            ( strcmp($filename, 'php.ini') == 0     )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Returns the content of a file.
     *
     * If the file does not exists or is not readable the function will return
     * false. Make sure that you double check this with a condition using triple
     * equals in order to avoid ambiguous results when the file exists, is
     * readable, but is empty.
     *
     * @param  string $fpath Relative or absolute path of the file.
     * @return string        Content of the file, false if not accessible.
     */
    public static function fileContent($fpath = '')
    {
        if (file_exists($fpath) && is_readable($fpath)) {
            return file_get_contents($fpath);
        }

        return false;
    }

    /**
     * Return the lines of a file as an array, it will automatically remove the new
     * line characters from the end of each line, and skip empty lines from the
     * list.
     *
     * @param  string $filepath Path to the file.
     * @return array            An array where each element is a line in the file.
     */
    public static function file_lines($filepath = '')
    {
        return @file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
