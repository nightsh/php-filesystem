<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Merged;

use Bit3\Filesystem\Filesystem;
use Bit3\Filesystem\File;
use Bit3\Filesystem\FilesystemException;
use Bit3\Filesystem\Util;

/**
 * Virtual filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class MergedFilesystem
    implements Filesystem
{
    /**
     * The root (/) filesystem.
     *
     * @var Filesystem
     */
    protected $root;

    protected $mounts;

    protected $map;

    /**
     * @param Filesystem $root
     */
    public function __construct(Filesystem $root = null)
    {
        $this->root   = $root;
        $this->mounts = array();
        $this->map    = array();
    }

    /**
     * Mount an filesystem to a specific path.
     *
     * @param Filesystem $filesystem
     * @param string     $path
     */
    public function mount(Filesystem $filesystem, $path)
    {
        $path                = $this->normalizeMountPath($path, true);
        $this->mounts[$path] = $filesystem;
        $this->map[$path]    = $filesystem;
        $path                = $this->normalizeMountPath($path);
        $this->map[$path]    = $filesystem;
        krsort($this->map);
    }

    public function umount($path)
    {
        $path = $this->normalizeMountPath($path, true);
        unset($this->mounts[$path]);
        unset($this->map[$path]);
        $path = $this->normalizeMountPath($path);
        unset($this->map[$path]);
    }

    public function mounts()
    {
        return array_filter(array_keys($this->mounts),
            function ($pattern) {
                return substr($pattern, 0, -1);
            });
    }

    protected function normalizeMountPath($path, $absolute = false)
    {
        $path = Util::normalizePath($path);

        if ($path[0] != '/') {
            $path = '/' . $path;
        }

        if (!$absolute) {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }

            $path .= '*';
        }

        return $path;
    }

    protected function searchFilesystem($path)
    {
        if ($path[0] != '/') {
            $path = '/' . $path;
        }

        foreach ($this->map as $pattern => $filesystem) {
            if (fnmatch($pattern, $path)) {
                // remove trailing *
                $pattern = preg_replace('#/\*$#', '', $pattern);

                return array($pattern, $filesystem);
            }
        }

        if ($this->root) {
            return array('', $this->root);
        }

        return array('', $this);
    }

    /**
     * Get the root (/) file node.
     *
     * @return File
     */
    public function getRoot()
    {
        if ($this->root) {
            return $this->root->getRoot();
        }

        return new VirtualFile('', '/', $this);
    }

    /**
     * Get a file object for the specific file.
     *
     * @param string $path
     *
     * @return File
     */
    public function getFile($path)
    {
        /** @var string $pattern */
        /** @var Filesystem $filesystem */
        list($pattern, $filesystem) = $this->searchFilesystem($path);

        $path = '/' . substr($path, strlen($pattern));

        return $filesystem->getFile($path);
    }

    /**
     * Returns available space on filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function getFreeSpace(File $path = null)
    {
        // TODO: Implement getFreeSpace() method.
    }

    /**
     * Returns the total size of a filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function getTotalSpace(File $path = null)
    {
        // TODO: Implement getTotalSpace() method.
    }

    /**
     * Find pathnames matching a pattern.
     *
     * @param string $pattern
     * @param int    $flags Use GLOB_* flags. Not all may supported on each filesystem.
     *
     * @return array<File>
     */
    public function glob($pattern, $flags = 0)
    {
        $pattern = Util::normalizePath($pattern);

        /*
        echo 'MergedFilesystem::glob(';
        ob_start();
        var_dump($pattern);
        $ob = trim(ob_get_contents());
        ob_end_clean();
        echo $ob;
        echo ', ';
        ob_start();
        var_dump($flags);
        $ob = trim(ob_get_contents());
        ob_end_clean();
        echo $ob;
        echo ")\n";
        */

        $files = array();
        foreach ($this->map as $mount => $fs) {
            // the mount itself match the pattern, means the pattern select a virtual structure
            if (fnmatch($pattern, substr($mount, 0, -1), $flags)) {
                // calculate the regexp from the pattern
                $regexp = Util::compilePatternToRegexp(Util::normalizePath('/' . $pattern));

                // remove trailing *
                $mount = preg_replace('#/\*$#', '', $mount);

                // only select matching part
                preg_match($regexp, $mount, $match);
                $path = $match[1];

                if (isset($this->mounts[$path])) {
                    if (!isset($files[$path])) {
                        $files[$path] = $this->mounts[$path]->getRoot();
                    }
                }
                else {
                    if (!isset($files[$path])) {
                        $files[$path] = new VirtualFile(dirname($path), basename($path), $this);
                    }
                }
            }

            // the pattern match the mount, means the pattern select a file inside the mount
            else if (fnmatch($mount, $pattern, $flags)) {
                $path = Util::stripPattern($mount, $pattern);
                var_dump('inner', $path);
                exit(1);
            }
        }

        ksort($files);

        /*
        echo '  return ';
        var_dump(array_values($files));
        */

        return array_values($files);
    }
}