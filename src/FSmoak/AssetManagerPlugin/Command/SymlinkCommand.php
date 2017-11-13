<?php

namespace FSmoak\AssetManagerPlugin\Command;

use FSmoak\AssetManagerPlugin\AssetManager;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SymlinkCommand extends AbstactCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-symlink')
			->setDescription("Symlink unchanged assets");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = $this->getIO();
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