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
 * Class CommitCommand
 * @package FSmoak\AssetManagerPlugin\Command
 */
class CommitCommand extends AbstractCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-commit')
			->setDescription("Commit assets to repository")
			->addOption("force","f",InputOption::VALUE_NONE,"Force delete and commit")
			->addOption("push","p",InputOption::VALUE_NONE,"Push with force");
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = $this->getIO();
		$config = $this->getAssetManager()->getConfig();
		
		if (empty($config->getEnvironment()))
		{
			$io->write("<error>Environment not configured. Please run 'asset-manager-init' first.</error>");
			exit;
		}
		$io->write("<info>Asset-Manager Environment:</info> ".$config->getEnvironment());
		
		$deletedAssets = $this->getAssetManager()->getDeletedAssets();
		if (!empty($deletedAssets))
		{
			$io->write("<info>The following assets have been removed:</info>");
			foreach ($deletedAssets AS $asset)
			{
				$io->write(" * " . $asset->getRelativePathname());
			}
			if (
				$input->getOption("force") ||
				$io->askConfirmation("<warning>Do you want to remove them from the repository?</warning> [Y/n]",true)
			)
			{
				$this->getAssetManager()->deleteAssetsFromRepository($deletedAssets);
			}
		}
		
		$changedAssets = $this->getAssetManager()->getChangedAssets();
		if (!empty($changedAssets))
		{
			$io->write("<info>The following assets have changed:</info>");
			foreach ($changedAssets AS $asset)
			{
				$io->write(" * " . $asset->getRelativePathname());
			}
			if (
				$input->getOption("force") ||
				$io->askConfirmation("<warning>Do you want to update them to the repository</warning> <info>(Method: ".$config->getMethod().")?</info> [Y/n]",true)
			)
			{
				$this->getAssetManager()->updateAssetsToRepository($changedAssets);
			}
		}
		
		if (!empty($deletedAssets) || !empty($changedAssets))
		{
			if (
				($input->getOption("force") && $input->getOption("push")) ||
				$io->askConfirmation("<warning>Do you want to push changes to the repository?</warning> [Y/n]",true)
			)
			{
				$this->getAssetManager()->pushRepository();
			}
		}
	}
}