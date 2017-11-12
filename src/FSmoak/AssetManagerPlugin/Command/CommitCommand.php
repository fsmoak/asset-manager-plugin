<?php

namespace FSmoak\AssetManagerPlugin\Command;

use FSmoak\AssetManagerPlugin\AssetManager;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class CommitCommand extends AbstactCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-commit')
			->setDescription("Commit assets to repository");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = $this->getIO();
		$config = $this->getAssetManager()->getConfig();
		
		if (empty($config->getEnviroment()))
		{
			$io->write("<error>Environment not configured. Please run 'asset-manager-init' first.</error>");
			exit;
		}
		$io->write("<info>Asset-Manager Environment:</info> ".$config->getEnviroment());
		
		
	}
}