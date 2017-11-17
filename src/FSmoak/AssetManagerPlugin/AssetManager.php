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

use function array_filter;
use Composer\Composer;
use Composer\IO\IOInterface;
use function file_exists;
use function mkdir;
use function str_repeat;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Finder\Finder;
use function substr;
use Symfony\Component\Finder\SplFileInfo;
use FSmoak\AssetManagerPlugin\Asset AS Asset;
use Symfony\Component\Process\Process;

/**
 * Class AssetManager
 * @package FSmoak\AssetManagerPlugin
 */
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
	public function getComposer()
	{
		return($this->composer);
	}

	/**
	 * @param Composer $composer
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function setComposer(Composer $composer)
	{
		$this->composer = $composer;
		return($this);
	}

	/**
	 * @return \Composer\IO\ConsoleIO|\Composer\IO\IOInterface
	 */
	public function getIo()
	{
		return $this->io;
	}

	/**
	 * @param IOInterface $io
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function setIo(IOInterface $io)
	{
		$this->io = $io;
		return($this);
	}

	/**
	 * @return Config
	 */
	public function getConfig()
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
		$finder = Finder::create()->files();
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
		$finder->in($includePaths);
		$excludePaths = array_filter(array_map(function($path) use ($finder) {
			if (substr($path,0,1) == "!" && substr($path,1,1) != "/")
			{
				$path = substr($path,1);
				$finder->notPath($path);
				return($path);
			}
			return(false);
		},$this->getConfig()->getPaths()));
		
		try
		{
			return(array_map(function(SplFileInfo $file) {
				return($file->getFileInfo(Asset::class));
			},iterator_to_array($finder)));
		}
		catch (Exception $e)
		{
			return([]);
		}
	}

	/**
	 * @return Asset[]
	 */
	public function getRepositoryAssets()
	{
		if ($this->refreshRepository())
		{
			$finder = new Finder();
			$finder->files()->in(AssetManager::CLONE_DIR);
			try
			{
				return(array_map(function(SplFileInfo $file){return($file->getFileInfo(Asset::class));},iterator_to_array($finder)));
			}
			catch (Exception $e)
			{
				return([]);
			}
		}
	}

	/**
	 * @return Asset[]
	 */
	public function getDeletedAssets()
	{
		$deleted = [];
		if ($this->refreshRepository())
		{
			$assets = $this->getRepositoryAssets();
			foreach ($assets AS $asset)
			{
				if (!$asset->existsInDeployed())
				{
					$deleted[] = $asset;
				}
			}
			return ($deleted);
		}
	}

	/**
	 * @return Asset[]
	 */
	public function getChangedAssets()
	{
		$changed = [];
		if ($this->refreshRepository())
		{
			$assets = $this->getLiveAssets();
			foreach ($assets AS $asset)
			{
				if ($asset->hasChanged())
				{
					$changed[] = $asset;
				}
			}
		}
		return($changed);
	}

	/**
	 * @param bool $includeSymlinks
	 * @return Asset[]
	 */
	public function getUnchangedAssets($includeSymlinks = true)
	{
		$unchanged = [];
		if ($this->refreshRepository())
		{
			$assets = $this->getLiveAssets();
			foreach ($assets AS $asset)
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
		}
		return($unchanged);
	}

	/**
	 * @param null $assets
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function deleteAssetsFromRepository($assets = null)
	{
		if (!$assets)
		{
			$assets = $this->getDeletedAssets();
		}
		foreach($assets AS $asset)
		{
			$this->command("git -C " . AssetManager::CLONE_DIR . " rm " . $asset->getDeployedPathname());
		}
		$this->commitRepository();
		return($this);
	}

	/**
	 * @param $assets
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
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
		$this->command("git -C ".self::getCloneDir()->path." add .");
		$this->command("git -C ".self::getCloneDir()->path." commit -a -m '".date("Y-m-d H:i:s",time())."'");
	}

	public function pushRepository()
	{
		$this->command("git -C ".self::getCloneDir()->path." push origin ".$this->getConfig()->getEnvironment());
	}

	/**
	 * @return bool
	 */
	public function refreshRepository()
	{
		if ($this->refreshRepositoryOnce)
		{
			return(true);
		}
		if ($repository = $this->getConfig()->getRepository())
		{
			$cloneDir = self::getCloneDir();
			$process = $this->command("git ls-remote -h " . $cloneDir->path);
			if ($process->getExitCode() != 0)
			{
				$this->getIo()->write("<warning>Local working copy does not exist!</warning>\n<info>Cloning from ".$repository." to ".$cloneDir->path.".</info>");
				$this->command("git clone ".$repository." ".$cloneDir->path);
			}
			$process = $this->command("git -C ".$cloneDir->path." clean -f -d");
			$process = $this->command("git -C ".$cloneDir->path." pull origin ".$this->getConfig()->getEnvironment());
			if ($process->getExitCode() != 0)
			{
				$this->getIo()->write("<warning>Environment branch does not exist!</warning>\n<info>Creating new Branch ".$this->getConfig()->getEnvironment().".</info>");
				$this->command("git -C ".$cloneDir->path." branch ".$this->getConfig()->getEnvironment());
			}
			$process = $this->command("git -C ".$cloneDir->path." checkout ".$this->getConfig()->getEnvironment()." -f");
			if ($process->getExitCode() == 0)
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

	/**
	 * @param string $command
	 * @return \Symfony\Component\Process\Process
	 */
	private function command($command)
	{
		$io = $this->getIO();
		
		$indent = str_repeat(" ",4);
		$dots = 0;
		$up = "\e[1A";
		$clear = "\e[2K";
		
		$io->write("<warning>$</warning>\e[4m " . $command."\e[0m", TRUE);
		$process = new Process($command);
		if (!in_array("--ansi",$_SERVER["argv"])) $io->write(str_repeat("\n",2),false); //2 newline because process callback will move 2 lines up and clear first
		$process
			->setTty(false)
			->setTimeout(null)
			->run(function($type, $buffer) use ($io,$indent,&$dots,$up,$clear) {
				if (!in_array("--ansi",$_SERVER["argv"])) $io->write(str_repeat($up.$clear,2),false);
				$buffer = explode("\n",trim($buffer));
				$output = $indent.end($buffer);
				if (Process::ERR === $type)
				{
					$io->write($output, true);
				}
				else
				{
					$io->write($output, true);
				}
				$dots++;
				if ($dots >= 4) $dots=0;
				if (!in_array("--ansi",$_SERVER["argv"])) $io->write($indent.str_repeat(".",$dots).str_repeat(" ",4-$dots), true);
			});
		if (!in_array("--ansi",$_SERVER["argv"])) $io->write($up.$clear.$indent,false);
		if ($process->getExitCode())
		{
			$io->write("<error>ExitCode: ".$process->getExitCode()."</error>", TRUE);
		}
		else
		{
			$io->write("<comment>ExitCode: ".$process->getExitCode()."</comment>", TRUE);
		}
		return ($process);
	}
}