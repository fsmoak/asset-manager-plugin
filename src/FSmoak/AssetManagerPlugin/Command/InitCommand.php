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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class InitCommand
 * @package FSmoak\AssetManagerPlugin\Command
 */
class InitCommand extends AbstractCommand
{
	protected function configure()
	{
		$this->setName('asset-manager-init')
			->setDescription("Initialize Asset-Manager");
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

		if (!$config->getRepository())
		{
			$io->write("<info>Asset Repository not set! Please provide valid path to a GIT LFS Repository.</info>");
		}
		try
		{
			$config->setRepository($io->askAndValidate("<warning>Asset Repository [" . $config->getRepository() . "]:</warning> ", function($repo) use ($io) {
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
		$io->write("<info>Using</info> " . $config->getRepository() . " <info>as repository from now on.</info>");

		if (empty($config->getPaths()))
		{
			$io->write("<error>No paths configured! Where are your assets?</error>");
		}
		while (true)
		{
			$paths = $config->getPaths();
			$table = new Table($output);
			$table
				->setHeaders([
					[new TableCell('<comment>Paths:</comment>', ['colspan' => 2])],
					['#', 'Path'],
				])
				->setRows(
					(!empty($paths) ?
						array_map(function($index,$value)
						{
							return([($index+1),$value]);
						},array_keys($paths),$paths)
						:
						[[new TableCell('<info>---</info>', ['colspan' => 2])]]
					)
				)
				->render();
			$answer = $io->ask("<warning>Add/Remove Path:</warning> ");
			if ($answer !== null)
			{
				if (is_numeric($answer))
				{
					$config->delPath(intval($answer)-1);
				}
				else
				{
					$config->addPath($answer);
				}
			}
			else
			{
				break;
			}
		}

		$config->setMethod(
			$this->getHelper("question")->ask(
				$input,
				$output,
				new ChoiceQuestion("<warning>Select a Method (default: ".$config->getMethod().")</warning>", [
					AssetManager::METHOD_SYMLINK,
					AssetManager::METHOD_COPY
				], $config->getMethod())));
		$io->write("<info>Using</info> " . $config->getMethod() . " <info>as method from now on.</info>");
		
		while (true)
		{
			$beforeCommits = $config->getBeforeCommit();
			$table = new Table($output);
			$table
				->setHeaders([
					[new TableCell('<comment>BeforeCommit:</comment>', ['colspan' => 2])],
					['#', 'Command'],
				])
				->setRows(
					(!empty($beforeCommits) ?
						array_map(function($index,$value)
						{
							return([($index+1),$value]);
						},array_keys($beforeCommits),$beforeCommits)
						:
						[[new TableCell('<info>---</info>', ['colspan' => 2])]]
					)
				)
				->render();
			$answer = $io->ask("<warning>Add/Remove BeforeCommit:</warning> ");
			if ($answer !== null)
			{
				if (is_numeric($answer))
				{
					$config->delBeforeCommit(intval($answer)-1);	
				}
				else
				{
					$config->addBeforeCommit($answer);
				}
			}
			else
			{
				break;
			}
		}

		while (true)
		{
			$afterDeploys = $config->getAfterDeploy();
			$table = new Table($output);
			$table
				->setHeaders([
					[new TableCell('<comment>AfterDeploy:</comment>', ['colspan' => 2])],
					['#', 'Command'],
				])
				->setRows(
					(!empty($afterDeploys) ?
						array_map(function($index,$value)
						{
							return([($index+1),$value]);
						},array_keys($afterDeploys),$afterDeploys)
						:
						[[new TableCell('<info>---</info>', ['colspan' => 2])]]
					)
				)
				->render();
			$answer = $io->ask("<warning>Add/Remove AfterDeploy:</warning> ");
			if ($answer !== null)
			{
				if (is_numeric($answer))
				{
					$config->delAfterDeploy(intval($answer)-1);
				}
				else
				{
					$config->addAfterDeploy($answer);
				}
			}
			else
			{
				break;
			}
		}

		while (true)
		{
			$variables = $config->getVariables();
			$table = new Table($output);
			$table
				->setHeaders([
					[new TableCell('<comment>Variables:</comment>', ['colspan' => 2])],
					['Key', 'Default Value'],
				])
				->setRows(
					(!empty($variables) ?
						array_map(function($key,$value)
						{
							return([$key,$value]);
						},array_keys($variables),$variables)
						:
						[[new TableCell('<info>---</info>', ['colspan' => 2])]]
					)
				)
				->render();
			$answer = $io->ask("<warning>Add/Remove Variable:</warning> ");
			if ($answer !== null)
			{
				if (array_key_exists($answer,$variables))
				{
					$config->delVariable($answer);
				}
				else
				{
					$config->addVariable($answer,$io->ask("<warning>Default Value for ".$answer.":</warning> "));
				}
			}
			else
			{
				break;
			}
		}
		
		if (!$config->getEnvironment())
		{
			$io->write("<info>Asset Environment not set! Please select a Branch name from the Repository.</info>");
		}
		try
		{
			$config->setEnvironment($io->askAndValidate("<warning>Asset Environment [" . $config->getEnvironment() . "]:</warning> ", function($env) use ($io) {
				if (!empty($env)) return($env);
				throw new \Exception($env . " is not a branch name!");
			}, NULL, $config->getEnvironment()));
		}
		catch (Exception $e)
		{
			$io->writeError("<error>".$e->getMessage()."</error>");
			exit;
		}
		$io->write("<info>Using</info> " . $config->getEnvironment() . " <info>as environment from now on.</info>");
		
		if ($config->getVariables())
		{
			while (TRUE)
			{
				$variables = $config->getVariables();
				$table = new Table($output);
				$table
					->setHeaders([
						[new TableCell('<comment>Environment Values:</comment>', ['colspan' => 3])],
						[
							'Key',
							'Default Value',
							"Environment Value (" . $config->getEnvironment() . ")"
						],
					])
					->setRows(
						array_map(function($key, $value) use ($config)
						{
							return ([
								$key,
								$value,
								$config->getEnvironmentVariable($key),
							]);
						},array_keys($variables),$variables)
					)
					->render();
				
				$answer = $io->ask("<warning>Set Environment Variable:</warning> ");
				if ($answer !== NULL)
				{
					if (array_key_exists($answer, $variables))
					{
						$config->setEnvironmentVariable($answer,$io->ask("<warning>Environment Value for ".$answer.":</warning> "));
					}
				}
				else
				{
					break;
				}
			}
		}
		
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