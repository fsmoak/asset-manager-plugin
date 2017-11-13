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

/**
 * Class Config
 * @package FSmoak\AssetManagerPlugin
 */
/**
 * Class Config
 * @package FSmoak\AssetManagerPlugin
 */
class Config
{
	/**
	 * @var string
	 */
	private $assetManagerJson = "asset-manager.json"; 
	
	/**
	 * @var string
	 */
	private $composerJson;

	/**
	 * @var array
	 */
	private $composerDefault = [
		"repository" => null,
		"paths" => [],
		"method" => AssetManager::METHOD_SYMLINK,
		"before-commit" => [],
		"after-deploy" => [],
		"variables" => [],
	];
	
	/**
	 * @var array
	 */
	private $composerConfig = [];

	/**
	 * @var array
	 */
	private $assetManagerDefault = [
		"environment" => null,
	];

	/**
	 * @var array 
	 */
	private $assetManagerConfig = [];
	
	/**
	 * Config constructor.
	 * @param $composerJson
	 */
	public function __construct($composerJson)
	{
		$this->loadComposerJson($composerJson);
		$this->loadAssetManagerJson();
	}

	/**
	 * @param $composerJson
	 */
	public function loadComposerJson($composerJson)
	{
		$config = [];
		if (file_exists($composerJson))
		{
			$this->composerJson = $composerJson;
			$json = json_decode(file_get_contents($this->composerJson),true);
			if (array_key_exists("asset-manager",$json))
			{
				$config = $json["asset-manager"];
			}
		}
		$this->composerConfig = array_merge($this->composerDefault,$config);
	}

	/**
	 * @param null $composerJson
	 * @return bool
	 */
	public function saveComposerJson($composerJson = null)
	{
		if (!$composerJson)
		{
			$composerJson = $this->composerJson;
		}
		$config = json_decode(file_get_contents($composerJson), TRUE);
		$config["asset-manager"] = $this->composerConfig;
		file_put_contents($composerJson,json_encode($config,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		return(true);
	}

	/**
	 */
	public function loadAssetManagerJson()
	{
		$json = [];
		if (file_exists($this->assetManagerJson))
		{
			$json = json_decode(file_get_contents($this->assetManagerJson),true);
		}
		$this->assetManagerConfig = array_merge($this->assetManagerDefault,$json);
	}

	/**
	 * @return bool
	 */
	public function saveAssetManagerJson()
	{
		file_put_contents($this->assetManagerJson,json_encode($this->assetManagerConfig,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		return(true);
	}
	
	/**
	 * @return string|null
	 */
	public function getRepository()
	{
		return($this->composerConfig["repository"]);
	}

	/**
	 * @param string $repository
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setRepository($repository)
	{
		$this->composerConfig["repository"] = $repository;
		return($this);
	}

	/**
	 * @return array[string]
	 */
	public function getPaths()
	{
		return($this->composerConfig["paths"]);
	}

	/**
	 * @param array $paths
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setPaths(array $paths)
	{
		$this->composerConfig["paths"] = $paths;
		return($this);
	}

	/**
	 * @param string $path
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function addPath($path)
	{
		$this->composerConfig["paths"][] = $path;
		return($this);
	}

	/**
	 * @param int $index
	 */
	public function delPath($index)
	{
		if (array_key_exists($index,$this->composerConfig["paths"]))
		{
			array_splice($this->composerConfig["paths"],$index,1);
		}
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return($this->composerConfig["method"]);
	}

	/**
	 * @param string $method
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMethod($method)
	{
		$this->composerConfig["method"] = $method;
		return($this);
	}

	/**
	 * @return mixed
	 */
	public function getEnvironment()
	{
		return($this->assetManagerConfig["environment"]);
	}

	/**
	 * @param $environment
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setEnvironment($environment)
	{
		$this->assetManagerConfig["environment"] = $environment;
		return($this);
	}

	/**
	 * @return array
	 */
	public function getBeforeCommit()
	{
		return($this->composerConfig["before-commit"]);
	}

	/**
	 * @param array $beforeCommit
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setBeforeCommit($beforeCommit)
	{
		$this->composerConfig["before-commit"] = $beforeCommit;
		return($this);
	}

	/**
	 * @param string $beforeCommit
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function addBeforeCommit($beforeCommit)
	{
		$this->composerConfig["before-commit"][] = $beforeCommit;
		return($this);
	}

	/**
	 * @param int $index
	 */
	public function delBeforeCommit($index)
	{
		if (array_key_exists($index,$this->composerConfig["before-commit"]))
		{
			array_splice($this->composerConfig["before-commit"],$index,1);
		}
		return($this);
	}

	/**
	 * @return array
	 */
	public function getAfterDeploy()
	{
		return($this->composerConfig["after-deploy"]);
	}

	/**
	 * @param array $afterDeploy
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setAfterDeploy($afterDeploy)
	{
		$this->composerConfig["after-deploy"] = $afterDeploy;
		return($this);
	}

	/**
	 * @param string $afterDeploy
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function addAfterDeploy($afterDeploy)
	{
		$this->composerConfig["after-deploy"][] = $afterDeploy;
		return($this);
	}

	/**
	 * @param int $index
	 */
	public function delAfterDeploy($index)
	{
		if (array_key_exists($index,$this->composerConfig["after-deploy"]))
		{
			array_splice($this->composerConfig["after-deploy"],$index,1);
		}
		return($this);
	}

	/**
	 * @return array
	 */
	public function getVariables()
	{
		return($this->composerConfig["variables"]);
	}

	/**
	 * @param array $variables
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setVariables($variables)
	{
		$this->composerConfig["variables"] = $variables;
		return($this);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function addVariable($name,$value)
	{
		$this->composerConfig["variables"][$name] = $value;
		return($this);
	}

	/**
	 * @param int $name
	 */
	public function delVariable($name)
	{
		if (array_key_exists($name,$this->composerConfig["variables"]))
		{
			unset($this->composerConfig["variables"][$name]);
		}
		return($this);
	}

	/**
	 * @param $name
	 * @return array|mixed|null
	 */
	public function getEnvironmentVariable($name)
	{
		if (array_key_exists($name,$this->assetManagerConfig))
		{
			return($this->assetManagerConfig[$name]);
		}
		return(null);
	}

	/**
	 * @param $name
	 * @param null $value
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setEnvironmentVariable($name, $value = null)
	{
		$this->assetManagerConfig[$name] = $value;
		return($this);
	}
}