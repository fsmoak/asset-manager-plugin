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

/**
 * Class Asset
 * @package FSmoak\AssetManagerPlugin
 */
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

	/**
	 * @return bool
	 */
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

	/**
	 * @return string
	 */
	public function getRepositoryPathname()
	{
		return(AssetManager::getCloneDir()->path.$this->getDeployedPathname());
	}

	/**
	 * @return string
	 */
	public function getRepositoryPath()
	{
		return(dirname($this->getRepositoryPathname()));
	}

	/**
	 * @return null|string|string[]
	 */
	public function getDeployedPathname()
	{
		return(preg_replace("/^".preg_quote(AssetManager::CLONE_DIR,"/")."/","",$this->getRelativePathname()));
	}

	/**
	 * @return string
	 */
	public function getDeployedPath()
	{
		return(dirname($this->getDeployedPathname()));
	}

	/**
	 * @return bool
	 */
	public function existsInRepository()
	{
		return(file_exists($this->getRepositoryPathname()));
	}

	/**
	 * @return bool
	 */
	public function existsInDeployed()
	{
		return(file_exists($this->getDeployedPathname()));
	}

	/**
	 * @return bool
	 */
	public function isLink()
	{
		return(is_link($this->getDeployedPathname()));
	}

	/**
	 * @param bool $move
	 * @param bool $force
	 * @return bool
	 */
	public function symlink($move = false, $force = false)
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
		if (!file_exists($this->getDeployedPath()))
		{
			mkdir($this->getDeployedPath(),0777,true);
		}
		exec("ln -s ".$this->getRelativePathFromDeployedToRepository()." ".$this->getDeployedPathname(),$output,$exitcode);
		return($exitcode);
	}

	/**
	 * @return bool
	 */
	public function moveFileToRepository()
	{
		if (!file_exists($this->getRepositoryPath()))
		{
			mkdir($this->getRepositoryPath(),0777,true);
		}
		return(rename($this->getDeployedPathname(),$this->getRepositoryPathname()));
	}

	/**
	 * @return bool
	 */
	public function copyToRepository()
	{
		if (!file_exists($this->getRepositoryPath()))
		{
			mkdir($this->getRepositoryPath(),0777,true);
		}
		return(copy($this->getDeployedPathname(),$this->getRepositoryPathname()));
	}

	/**
	 * @return bool
	 */
	public function copyToDeployed()
	{
		if ($this->existsInDeployed() && $this->isLink())
		{
			unlink($this->getDeployedPathname());
		}
		if (!file_exists($this->getDeployedPath()))
		{
			mkdir($this->getDeployedPath(),0777,true);
		}
		return(copy($this->getRepositoryPathname(),$this->getDeployedPathname()));
	}

	/**
	 * @return string
	 */
	public function getRelativePathFromDeployedToRepository()
	{
		return(Path::makeRelative($this->getRepositoryPathname(),dirname($this->getDeployedPathname())));
	}

	/**
	 * @return string
	 */
	public function getRelativePathFromRepositoryToDeployed()
	{
		return(Path::makeRelative($this->getDeployedPathname(),dirname($this->getRepositoryPathname())));
	}
}