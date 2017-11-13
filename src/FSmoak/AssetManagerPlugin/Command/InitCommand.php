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

use Exception;
use FSmoak\AssetManagerPlugin\AssetManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class InitCommand extends AbstractCommand
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

		if (!$config->getRepository())
		{
			$io->write("<info>Asset Repository not set! Please provide valid path to a GIT LFS Repository.</info>");
		}
		try
		{
			$config->setRepository($io->askAndValidate("Asset Repository [" . $config->getRepository() . "]: ", function($repo) use ($io) {
				$cmd = "git ls-remote -h " . $repo;
				$io->write("<comment>Checking repository: $ " . $cmd . "</comment>");
				exec("git ls-remote -h " . $repo, $output, $exitcode);
				if ($exitcode == 0)
				{
					return ($repo);
				}
				throw new \Exception($repo . " is not a git repository!");
			}, NULL, $config->getRepository()));
		}
		catch (Exception $e)
		{
			$io->writeError("<error>".$e->getMessage()."</error>");
			exit;
		}
		$io->write("<info>Using " . $config->getRepository() . " as repository from now on.</info>");

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
			if ($addPath && substr($addPath,0,1) != "/")
			{
				$config->addPath($addPath);
			}
		}

		$config->setMethod(
			$this->getHelper("question")->ask(
				$input,
				$output,
				new ChoiceQuestion("Select a Method (default: ".$config->getMethod().")", [
					AssetManager::METHOD_SYMLINK,
					AssetManager::METHOD_COPY
				], $config->getMethod())));

		if (!$config->getEnviroment())
		{
			$io->write("<info>Asset Environment not set! Please select a Branch name from the Repository.</info>");
		}
		try
		{
			$config->setEnviroment($io->askAndValidate("Asset Environment [" . $config->getEnviroment() . "]: ", function($env) use ($io) {
				if (!empty($env)) return($env);
				throw new \Exception($env . " is not a branch name!");
			}, NULL, $config->getEnviroment()));
		}
		catch (Exception $e)
		{
			$io->writeError("<error>".$e->getMessage()."</error>");
			exit;
		}
		$io->write("<info>Using " . $config->getEnviroment() . " as environment from now on.</info>");
		
		if (file_exists(".gitignore") && !in_array("asset-manager.json",explode("\n",file_get_contents(".gitignore"))))
		{
			if ($io->askConfirmation("Add asset-manager.json & .asset-manager/ to .gitignore? [Y/n]",true))
			{
				file_put_contents(".gitignore","\nasset-manager.json\n.asset-manager/",FILE_APPEND);
			}
		}
		
		$config->saveComposerJson();
		$config->saveAssetManagerJson();
	}
}