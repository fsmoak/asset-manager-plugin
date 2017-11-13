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


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SymlinkCommand extends AbstractCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-symlink')
			->setDescription("Symlink unchanged assets");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = $this->getIO();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$config = $this->getAssetManager()->getConfig();

		$unchangedAssets = $this->getAssetManager()->getUnchangedAssets(false);
		if (!empty($unchangedAssets))
		{
			$io->write("<info>The following assets will replaced with symlinks:</info>");
			foreach ($unchangedAssets AS $asset)
			{
				$io->write(" * " . $asset->getRelativePathname()." => ".$asset->getRelativePathFromDeployedToRepository());
			}
			if ($io->askConfirmation("Do you want to replace there assets with symlinks? [Y/n]",true))
			{
				foreach ($unchangedAssets AS $asset)
				{
					$asset->symlink(false,true);
				}
			}
		}
	}
}