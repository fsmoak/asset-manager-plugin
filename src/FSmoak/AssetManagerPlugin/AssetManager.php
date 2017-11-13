<?php
namespace FSmoak\AssetManagerPlugin;

use function array_filter;
use Composer\Composer;
use Composer\IO\IOInterface;
use function copy;
use function file_exists;
use function mkdir;
use function move_uploaded_file;
use function substr;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use FSmoak\AssetManagerPlugin\Asset AS Asset;

class AssetManager
{
	const METHOD_SYMLINK = "symlink";
	const METHOD_COPY = "copy";
	const CLONE_DIR = ".asset-manager/";
	
	/**
	 * @var Composer
	 */
	private $composer;
	/**
	 * @var IOInterface
	 */
	private $io;
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var string
	 */
	private $composerJson;

	/**
	 * @var bool
	 */
	private $refreshRepositoryOnce = false;
	
	/**
	 * AssetManager constructor.
	 * @param \Composer\Composer $composer
	 * @param \Composer\IO\IOInterface $io
	 */
	public function __construct(Composer $composer, IOInterface $io)
	{
		$this->setComposer($composer);
		$this->setIo($io);
		$this->setConfig(new Config($composer->getConfig()->getConfigSource()->getName()));
	}	
	
	/**
	 * @return \Composer\Composer
	 */
	public function getComposer(): \Composer\Composer
	{
		return $this->composer;
	}

	/**
	 * @param \Composer\Composer $composer
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function setComposer(\Composer\Composer $composer)
	{
		$this->composer = $composer;
		return($this);
	}

	/**
	 * @return \Composer\IO\ConsoleIO
	 */
	public function getIo(): \Composer\IO\ConsoleIO
	{
		return $this->io;
	}

	/**
	 * @param \Composer\IO\IOInterface $io
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function setIo(\Composer\IO\IOInterface $io)
	{
		$this->io = $io;
		return($this);
	}

	/**
	 * @return Config
	 */
	public function getConfig(): Config
	{
		return $this->config;
	}

	/**
	 * @param Config $config
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function setConfig(Config $config)
	{
		$this->config = $config;
		return($this);
	}
	
	/**
	 * Yeah I don't know let' see
	 * 
	 */
	public function activate()
	{
	}

	/**
	 * @return Asset[]
	 */
	public function getLiveAssets()
	{
		$includePaths = array_filter($this->getConfig()->getPaths(),function($path){
			if (
				substr($path,0,1) == "/" ||
				substr($path,0,1) == "!"
			)
			{
				return(false);
			}
			if (is_dir($path))
			{
				return(true);
			} 
			elseif ($glob = glob($path, (defined('GLOB_BRACE') ? GLOB_BRACE : 0) | GLOB_ONLYDIR)) 
			{
				return(true);
			}
			return(false);
		});
		$excludePaths = array_filter($this->getConfig()->getPaths(),function($path){
			if (substr($path,0,1) == "!" && substr($path,1,1) != "/")
			{
				return(substr($path,1));
			}
			return(false);
		});
		$finder = new Finder();
		$finder->files()->in($includePaths)->exclude($excludePaths);
		return(array_map(function(SplFileInfo $file) {
			return($file->getFileInfo(Asset::class));
		},iterator_to_array($finder)));
	}

	/**
	 * @return Asset[]
	 */
	public function getRepositoryAssets()
	{
		$this->refreshRepository();
		$finder = new Finder();
		$finder->files()->in(AssetManager::CLONE_DIR);
		return(array_map(function(SplFileInfo $file){return($file->getFileInfo(Asset::class));},iterator_to_array($finder)));
	}

	/**
	 * @return Asset[]
	 */
	public function getDeletedAssets()
	{
		$deleted = [];
		$this->refreshRepository();
		$assets = $this->getRepositoryAssets();
		foreach($assets AS $asset)
		{
			if (!$asset->existsInDeployed())
			{
				$deleted[] = $asset;
			}
		}
		return($deleted);
	}

	/**
	 * @return Asset[]
	 */
	public function getChangedAssets()
	{
		$changed = [];
		$this->refreshRepository();
		$assets = $this->getLiveAssets();
		foreach($assets AS $asset)
		{
			if ($asset->hasChanged())
			{
				$changed[] = $asset;
			}
		}
		return($changed);
	}

	/**
	 * @return Asset[]
	 */
	public function getUnchangedAssets($includeSymlinks = true)
	{
		$unchanged = [];
		$this->refreshRepository();
		$assets = $this->getLiveAssets();
		foreach($assets AS $asset)
		{
			if (!$asset->hasChanged())
			{
				if (!$includeSymlinks && $asset->isLink())
				{
					continue;
				}
				$unchanged[] = $asset;
			}
		}
		return($unchanged);
	}

	public function deleteAssetsFromRepository($assets = null)
	{
		if (!$assets)
		{
			$assets = $this->getDeletedAssets();
		}
		foreach($assets AS $asset)
		{
			exec("git -C " . AssetManager::CLONE_DIR . " rm " . $asset->getDeployedPathname(), $output, $exitcode);
		}
		$this->commitRepository();
		return($this);
	}
	
	public function updateAssetsToRepository($assets)
	{
		if (!$assets)
		{
			$assets = $this->getChangedAssets();
		}
		foreach($assets AS $asset)
		{
			if (file_exists($asset->getDeployedPathname()))
			{
				if (!file_exists($asset->getRepositoryPath()))
				{
					mkdir($asset->getRepositoryPath(),0777,true);
				}
				
				switch ($this->getConfig()->getMethod())
				{
					case AssetManager::METHOD_SYMLINK:
						$asset->symlink(true);
						break;
					case AssetManager::METHOD_COPY:
						$asset->copyToRepository();
						break;
				}
			}
		}
		$this->commitRepository();
		return($this);
	}
	
	public function commitRepository()
	{
		exec("git -C ".self::getCloneDir()->path." add .");
		exec("git -C ".self::getCloneDir()->path." commit -a -m '".date("Y-m-d H:i:s",time())."'");
	}

	public function pushRepository()
	{
		exec("git -C ".self::getCloneDir()->path." push origin ".$this->getConfig()->getEnviroment());
	}
	
	public function refreshRepository()
	{
		if ($this->refreshRepositoryOnce)
		{
			return(true);
		}
		if ($repository = $this->getConfig()->getRepostitory())
		{
			$cloneDir = self::getCloneDir();
			exec("git ls-remote -h " . $cloneDir->path, $output, $exitcode);
			if ($exitcode != 0)
			{
				$this->getIo()->write("<error>Local working copy does not exist!</error> <info>Cloning from ".$repository." to ".$cloneDir->path.".</info>");
				exec("git clone ".$repository." ".$cloneDir->path);
			}
			exec("git -C ".$cloneDir->path." pull origin ".$this->getConfig()->getEnviroment(),$output,$exitcode);
			if ($exitcode != 0)
			{
				$this->getIo()->write("<error>Environment branch does not exist!</error> <info>Creating new Branch ".$this->getConfig()->getEnviroment().".</info>");
				exec("git -C ".$cloneDir->path." branch ".$this->getConfig()->getEnviroment());
			}
			exec("git -C ".$cloneDir->path." checkout ".$this->getConfig()->getEnviroment(),$output,$exitcode);
			if ($exitcode == 0)
			{
				$this->refreshRepositoryOnce = true;
				return (TRUE);
			}
		}
		return(false);
	}

	/**
	 * @return \Directory
	 */
	static function getCloneDir()
	{
		if (!file_exists(AssetManager::CLONE_DIR))
		{
			mkdir(AssetManager::CLONE_DIR);
		}
		return(dir(AssetManager::CLONE_DIR));
	}
}