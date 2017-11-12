<?php
namespace FSmoak\AssetManagerPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;

class AssetManagerPlugin implements PluginInterface, Capable
{
	/**
	 * @var AssetManager
	 */
	private $assetManager;

	/**
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function getAssetManager(): \FSmoak\AssetManagerPlugin\AssetManager
	{
		return $this->assetManager;
	}

	/**
	 * @param \FSmoak\AssetManagerPlugin\AssetManager $assetManager
	 */
	public function setAssetManager(\FSmoak\AssetManagerPlugin\AssetManager $assetManager)
	{
		$this->assetManager = $assetManager;
	}
	
	public function activate(Composer $composer, IOInterface $io)
	{
		$io->write("Initializing Asset-Manager Plugin...");
			
		$this->setAssetManager(new AssetManager($composer,$io));
		
		$this->getAssetManager()->activate();
	}

	public function getCapabilities()
	{
		return array(
			'Composer\Plugin\Capability\CommandProvider' => 'FSmoak\AssetManagerPlugin\CommandProvider',
		);
	}
}