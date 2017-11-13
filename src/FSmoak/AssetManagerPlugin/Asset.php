<?php
/**
 *
 *  * This file is part of Asset-Manager Composer-Plugin
 *  *
 *  * (c) FSmoak <marieschreiber84@gmail.com>
 *  *
 *  * For the full copyright and license information, please view the LICENSE
 *  * file that was distributed with this source code.
 *  
 */

namespace FSmoak\AssetManagerPlugin;

use const DIRECTORY_SEPARATOR;
use Exception;

use function file_exists;
use function getcwd;
use function str_replace;
use Symfony\Component\Finder\SplFileInfo;
use function unlink;
use Webmozart\PathUtil\Path;

class Asset extends SplFileInfo
{
	/**
	 * Asset constructor.
	 * @param $file
	 * @throws \Exception
	 */
	public function __construct($file)
	{
		$cwd = getcwd()."/";
		while (stripos($file,DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) !== false)
		{
			$file = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $file);
		}
		if (Path::isBasePath($cwd,$file))
		{
			throw new Exception($file."is not in ".$cwd);
		}
		parent::__construct($file, dirname($file), $file);
	}
	public function hasChanged()
	{
		if (!$this->existsInDeployed() || !$this->existsInRepository())
		{
			return(true);
		}
		if (md5_file($this->getRelativePathname()) == md5_file($this->getRepositoryPathname()))
		{
			return(false);
		}
		return(true);
	}
	public function getRepositoryPathname()
	{
		return(AssetManager::getCloneDir()->path.$this->getDeployedPathname());
	}
	public function getRepositoryPath()
	{
		return(dirname($this->getRepositoryPathname()));
	}
	public function getDeployedPathname()
	{
		return(preg_replace("/^".preg_quote(AssetManager::CLONE_DIR,"/")."/","",$this->getRelativePathname()));
	}
	public function getDeployedPath()
	{
		return(dirname($this->getDeployedPathname()));
	}
	public function existsInRepository()
	{
		return(file_exists($this->getRepositoryPathname()));
	}
	public function existsInDeployed()
	{
		return(file_exists($this->getDeployedPathname()));
	}
	public function isLink()
	{
		return(is_link($this->getDeployedPathname()));
	}

	public function symlink($move = false,$force = false)
	{
		if ($move)
		{
			$this->moveFileToRepository();
		}
		if (file_exists($this->getDeployedPathname()))
		{
			if ($force)
			{
				unlink($this->getDeployedPathname());
			}
			else
			{
				return(false);
			}
		}
		exec("ln -s ".$this->getRelativePathFromDeployedToRepository()." ".$this->getDeployedPathname(),$output,$exitcode);
		return($exitcode);
	}
	public function moveFileToRepository()
	{
		rename($this->getDeployedPathname(),$this->getRepositoryPathname());
	}
	public function copyToRepository()
	{
		return(copy($this->getDeployedPathname(),$this->getRepositoryPathname()));
	}
	public function copyToDeployed()
	{
		if ($this->existsInDeployed() && $this->isLink())
		{
			unlink($this->getDeployedPathname());
		}
		return(copy($this->getRepositoryPathname(),$this->getDeployedPathname()));
	}
	
	public function getRelativePathFromDeployedToRepository()
	{
		return(Path::makeRelative($this->getRepositoryPathname(),dirname($this->getDeployedPathname())));
	}
	public function getRelativePathFromRepositoryToDeployed()
	{
		return(Path::makeRelative($this->getDeployedPathname(),dirname($this->getRepositoryPathname())));
	}
}