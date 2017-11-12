<?php

namespace FSmoak\AssetManagerPlugin\Command;

use FSmoak\AssetManagerPlugin\AssetManager;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class InitCommand extends AbstactCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-init')
			->setDescription("Initialize Asset-Manager");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = $this->getIO();
		$config = $this->getAssetManager()->getConfig();

		if (!$config->getRepostitory())
		{
			$io->write("<info>Asset Repository not set! Please provide valid path to a GIT LFS Repository.</info>");
		}
		$config->setRepostitory($io->askAndValidate("Asset Repository [" . $config->getRepostitory() . "]: ", function($repo) use ($io) {
			$cmd = "git ls-remote -h " . $repo;
			$io->write("<comment>Checking repository: $ " . $cmd . "</comment>");
			exec("git ls-remote -h " . $repo, $output, $exitcode);
			if ($exitcode == 0)
			{
				return ($repo);
			}
			throw new \Exception($repo . " is not a git repository!");
		}, NULL, $config->getRepostitory()));
		$io->write("<info>Using " . $config->getRepostitory() . " as repository from now on.</info>");

		if (empty($config->getPaths()))
		{
			$io->write("<error>No paths configured! Where are your assets?</error>");
		}
		$addPath = TRUE;
		while (empty($config->getPaths()) || $addPath)
		{
			if (!empty($config->getPaths()))
			{
				$io->write("<comment>Paths: \n * " . implode("\n * ", $config->getPaths()) . "</comment>");
			}
			$addPath = $io->ask("Add Path: ");
			if ($addPath)
			{
				$config->addPath($addPath);
			}
		}

		$config->setMethod(
			$this->getHelper("question")->ask(
				$input,
				$output,
				new ChoiceQuestion("Select a Method (default: ".AssetManager::METHOD_SYMLINK.")", [
					AssetManager::METHOD_SYMLINK,
					AssetManager::METHOD_COPY
				], $config->getMethod())));

		$config->saveComposerJson();
	}
}