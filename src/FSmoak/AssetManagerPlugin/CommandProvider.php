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

