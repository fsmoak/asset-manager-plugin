<?php
namespace FSmoak\AssetManagerPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;

class AssetManager
{
	const METHOD_SYMLINK = "symlink";
	const METHOD_COPY = "copy";
	
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
}