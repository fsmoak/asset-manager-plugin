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
	public function getAssetManager()
	{
		return $this->assetManager;
	}

	/**
	 * @param \FSmoak\AssetManagerPlugin\AssetManager $assetManager
	 */
	public function setAssetManager($assetManager)
	{
		$this->assetManager = $assetManager;
	}
	
	public function activate(Composer $composer, IOInterface $io)
	{
		$io->write("Initializing Asset-Manager Plugin...",true,$io::VERY_VERBOSE);
			
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