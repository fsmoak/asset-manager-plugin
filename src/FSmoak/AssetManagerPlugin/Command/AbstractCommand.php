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

namespace FSmoak\AssetManagerPlugin\Command;
use Composer\Command\BaseCommand;
use FSmoak\AssetManagerPlugin\AssetManager;
use FSmoak\AssetManagerPlugin\AssetManagerPlugin;

/**
 * Class AbstractCommand
 * @package FSmoak\AssetManagerPlugin\Command
 */
abstract class AbstractCommand extends BaseCommand
{
	/**
	 * AbstractCommand constructor.
	 * @param null $name
	 */
	public function __construct($name = NULL)
	{
		parent::__construct($name);
	}
	
	/**
	 * @var AssetManager
	 */
	private $assetManager;

	/**
	 * @return \FSmoak\AssetManagerPlugin\AssetManager
	 */
	public function getAssetManager()
	{
		if ($this->assetManager) return($this->assetManager);
		foreach($this->getComposer()->getPluginManager()->getPlugins() AS $plugin)
		{
			if ($plugin instanceof AssetManagerPlugin)
			{
				$this->assetManager = $plugin->getAssetManager();
			}
		}
		return($this->getAssetManager());
	}

	/**
	 * Sleep Function to fix Output of very much text in a loop
	 */
	public function massOutputSleepFix()
	{
		usleep(10000);
	}
}