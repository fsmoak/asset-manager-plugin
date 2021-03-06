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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class SymlinkCommand
 * @package FSmoak\AssetManagerPlugin\Command
 */
class SymlinkCommand extends AbstractCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-symlink')
			->setDescription("Symlink unchanged assets")
			->addOption("force","f",InputOption::VALUE_NONE,"Force override");
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = $this->getIO();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$config = $this->getAssetManager()->getConfig();

		if (empty($config->getEnvironment()))
		{
			$io->write("<error>Environment not configured. Please run 'asset-manager-init' first.</error>");
			exit;
		}
		$io->write("<info>Asset-Manager Environment:</info> ".$config->getEnvironment());
		
		$unchangedAssets = $this->getAssetManager()->getUnchangedAssets(false);
		if (!empty($unchangedAssets))
		{
			$io->write("<info>The following assets will replaced with symlinks:</info>");
			foreach ($unchangedAssets AS $asset)
			{
				$io->write(" * <info>" . $asset->getRelativePathname()."</info> => <comment>".$asset->getRelativePathFromDeployedToRepository()."</comment>");
				$this->massOutputSleepFix();
			}
			if (
				$input->getOption("force") ||
				$io->askConfirmation("<warning>Do you want to replace there assets with symlinks?</warning> [Y/n]",true)
			)
			{
				foreach ($unchangedAssets AS $asset)
				{
					$io->write("<info>".$asset->getRelativePathname()."</info> => <comment>".$asset->getRelativePathFromDeployedToRepository()."</comment>");
					$asset->symlink(false,true);
				}
			}
		}
	}
}