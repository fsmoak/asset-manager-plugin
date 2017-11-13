<?php

namespace FSmoak\AssetManagerPlugin\Command;

use FSmoak\AssetManagerPlugin\AssetManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends AbstactCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-deploy')
			->setDescription("Deploy assets from repository")
			->addOption("force","f",InputOption::VALUE_NONE,"Force override");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = $this->getIO();
		$config = $this->getAssetManager()->getConfig();

		$repositoryAssets = $this->getAssetManager()->getRepositoryAssets();
		if (!empty($repositoryAssets))
		{
			$io->write("<info>The following assets will be deployed:</info>");
			foreach ($repositoryAssets AS $asset)
			{
				$io->write(" * " . $asset->getDeployedPathname());
			}
			if (
				$input->getOption("force") ||
				$io->askConfirmation("Do you want to deploy there assets (Method: ".$config->getMethod().")? [Y/n]",true)
			)
			{
				foreach ($repositoryAssets AS $asset)
				{
					$io->write($asset->getDeployedPathname()." - ",false);
					
					if (!$asset->existsInDeployed())
					{
						switch ($config->getMethod())
						{
							case AssetManager::METHOD_SYMLINK:
								$io->write("<info>move (changed to symlink)</info>");
								$asset->symlink(FALSE, TRUE);
								break;
							case AssetManager::METHOD_COPY:
								$io->write("<info>copy (changed to copy)</info>");
								$asset->copyToDeployed();
								break;
						}
					}
					else
					{
						if (!$asset->hasChanged())
						{
							switch ($config->getMethod())
							{
								case AssetManager::METHOD_SYMLINK:
									if (!$asset->isLink())
									{
										$io->write("<info>same (changed to symlink)</info>");
										$asset->symlink(FALSE, TRUE);
									}
									else
									{
										$io->write("<comment>skip</comment>");
									}
									break;
								case AssetManager::METHOD_COPY:
									if ($asset->isLink())
									{
										$io->write("<info>same (changed to copy)</info>");
										$asset->copyToDeployed();
									}
									else
									{
										$io->write("<comment>skip</comment>");
									}
									break;
							}
						}
						else
						{
							if (
								$input->getOption("force") ||
								$io->ask("already exists and is different from repository!\nDo you want to Override it [y/N]",false)
							)
							{
								switch ($config->getMethod())
								{
									case AssetManager::METHOD_SYMLINK:
										$io->write("<info>changed to symlink</info>");
										$asset->symlink(FALSE, TRUE);
										break;
									case AssetManager::METHOD_COPY:
										$io->write("<info>changed to copy</info>");
										$asset->copyToDeployed();
										break;
								}
							}
						}
					}
				}
			}
		}
	}
}