<?php
namespace FSmoak\AssetManagerPlugin\Command;
use Composer\Command\BaseCommand;
use FSmoak\AssetManagerPlugin\AssetManager;

abstract class AbstactCommand extends BaseCommand
{
	public function __construct($name = NULL)
	{
		parent::__construct($name);
	}
	
	/**
	 * @var AssetManager
	 */
	private $assetManager;
	public function getAssetManager()
	{
		if ($this->assetManager) return($this->assetManager);
		foreach($this->getComposer()->getPluginManager()->getPlugins() AS $plugin)
		{
			if ($plugin instanceof \FSmoak\AssetManagerPlugin\AssetManagerPlugin)
			{
				$this->assetManager = $plugin->getAssetManager();
			}
		}
		return($this->getAssetManager());
	}
}