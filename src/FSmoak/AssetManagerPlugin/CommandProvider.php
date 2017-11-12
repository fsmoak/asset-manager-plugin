<?php
namespace FSmoak\AssetManagerPlugin;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use FSmoak\AssetManagerPlugin\Command\CommitCommand;
use FSmoak\AssetManagerPlugin\Command\InitCommand;

class CommandProvider implements CommandProviderCapability
{
	public function getCommands()
	{
		return([
			new InitCommand,
			new CommitCommand,
		]); 
	}
}

