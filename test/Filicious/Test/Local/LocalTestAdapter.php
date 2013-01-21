<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 * @link    http://filicious.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Filicious\Test\Local;

use Filicious\Local\LocalFilesystemConfig;

use Filicious\Test\TestAdapter;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-10-17 at 10:24:36.
 */
class LocalTestAdapter implements TestAdapter
{
	public $basepath;
	
	public function __construct($path)
	{
		$this->basepath = $path . '/';
	}

	/**
	 * @return bool
	 */
	public function isSymlinkSupported()
	{
		return function_exists('symlink');
	}

	public function createDirectory($path)
	{
		mkdir($this->basepath . $path);
	}

	public function putContents($path, $content)
	{
		file_put_contents($this->basepath . $path, $content);
	}

	public function getContents($path)
	{
		return file_get_contents($this->basepath . $path);
	}

	public function deleteFile($path)
	{
		unlink($this->basepath . $path);
	}

	public function deleteDirectory($path)
	{
		rmdir($this->basepath . $path);
	}

	public function symlink($target, $link)
	{
		symlink($target, $this->basepath . $link);
	}

	public function isFile($path)
	{
		clearstatcache();
		return is_file($this->basepath . $path);
	}

	public function isDirectory($path)
	{
		clearstatcache();
		return is_dir($this->basepath . $path);
	}

	public function isLink($path)
	{
		clearstatcache();
		return is_link($this->basepath . $path);
	}

	public function exists($path)
	{
		clearstatcache();
		return file_exists($this->basepath . $path);
	}

	public function getATime($path)
	{
		clearstatcache();
		return new \DateTime('@' . fileatime($this->basepath . $path));
	}

	public function getCTime($path)
	{
		clearstatcache();
		return new \DateTime('@' . filectime($this->basepath . $path));
	}

	public function getMTime($path)
	{
		clearstatcache();
		return new \DateTime('@' . filemtime($this->basepath . $path));
	}

	public function getFileSize($path)
	{
		clearstatcache();
		return filesize($this->basepath . $path);
	}

	public function getOwner($path)
	{
		clearstatcache();
		return fileowner($this->basepath . $path);
	}

	public function getGroup($path)
	{
		clearstatcache();
		return filegroup($this->basepath . $path);
	}

	public function getMode($path)
	{
		clearstatcache();
		return fileperms($this->basepath . $path);
	}

	public function stat($path)
	{
		clearstatcache();
		return stat($this->basepath . $path);
	}

	public function scandir($path)
	{
		clearstatcache();
		return scandir($this->basepath . $path);
	}
}
