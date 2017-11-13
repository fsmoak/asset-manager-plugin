<?php
namespace FSmoak\AssetManagerPlugin;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use FSmoak\AssetManagerPlugin\Command\CommitCommand;
use FSmoak\AssetManagerPlugin\Command\DeployCommand;
use FSmoak\AssetManagerPlugin\Command\InitCommand;
use FSmoak\AssetManagerPlugin\Command\SymlinkCommand;

class CommandProvider implements CommandProviderCapability
{
	public function getCommands()
	{
		return([
			new InitCommand,
			new CommitCommand,
			new SymlinkCommand,
			new DeployCommand,
		]); 
	}
}

