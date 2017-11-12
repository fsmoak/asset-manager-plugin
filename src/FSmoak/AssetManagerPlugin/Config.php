<?php
namespace FSmoak\AssetManagerPlugin;

/**
 * Class Config
 * @package FSmoak\AssetManagerPlugin
 */
class Config
{
	/**
	 * @var string
	 */
	private $composerJson;

	/**
	 * @var array
	 */
	private $default = [
		"repository" => null,
		"paths" => [],
		"method" => AssetManager::METHOD_SYMLINK,
		"mysql_enabled" => false,
		"mysql_file" => ".asset-manager.sql",
		"mysql_dump_command" => "mysqldump -h {host} -u {user} -p{pass} {db} > {file}",
		"mysql_import_command" => "mysql -h {host} -u {user} -p{pass} {db} < {file}",
	];
	
	/**
	 * @var array
	 */
	private $config = [];
	
	/**
	 * Config constructor.
	 * @param $composerJson
	 */
	public function __construct($composerJson)
	{
		$this->loadComposerJson($composerJson);
	}

	/**
	 * @param $composerJson
	 */
	public function loadComposerJson($composerJson)
	{
		$assetManagerJson = [];
		if (file_exists($composerJson))
		{
			$this->composerJson = $composerJson;
			$json = json_decode(file_get_contents($this->composerJson),true);
			if (array_key_exists("asset-manager",$json))
			{
				$assetManagerJson = $json["asset-manager"];
			}
		}
		$this->config = array_merge($this->default,$assetManagerJson);
	}
	
	public function saveComposerJson($composerJson = null)
	{
		if (!$composerJson)
		{
			$composerJson = $this->composerJson;
		}
		$config = json_decode(file_get_contents($composerJson), TRUE);
		$config["asset-manager"] = $this->config;
		file_put_contents($composerJson,json_encode($config,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		return(true);
	}

	/**
	 * @return string|null
	 */
	public function getRepostitory()
	{
		return($this->config["repository"]);
	}

	/**
	 * @param string $repository
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setRepostitory(string $repository)
	{
		$this->config["repository"] = $repository;
		return($this);
	}

	/**
	 * @return array[string]
	 */
	public function getPaths()
	{
		return($this->config["paths"]);
	}

	/**
	 * @param array $paths
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setPaths(array $paths)
	{
		$this->config["paths"] = $paths;
		return($this);
	}

	/**
	 * @param string $path
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function addPath(string $path)
	{
		$this->config["paths"][] = $path;
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return($this->config["method"]);
	}

	/**
	 * @param string $method
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMethod(string $method)
	{
		$this->config["method"] = $method;
		return($this);
	}

	/**
	 * @return bool
	 */
	public function getMysqlEnabled()
	{
		return($this->config["mysql_enabled"]);
	}

	/**
	 * @param bool $mysql_enabled
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlEnabled(bool $mysql_enabled)
	{
		$this->config["mysql_enabled"] = $mysql_enabled;
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMysqlFile()
	{
		return($this->config["mysql_file"]);
	}

	/**
	 * @param string $mysql_file
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlFile(string $mysql_file)
	{
		$this->config["mysql_file"] = $mysql_file;
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMysqlDumpCommand()
	{
		return($this->config["mysql_dump_command"]);
	}

	/**
	 * @param string $mysql_dump_command
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlDumpCommand(string $mysql_dump_command)
	{
		$this->config["mysql_dump_command"] = $mysql_dump_command;
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMysqlImportCommand()
	{
		return($this->config["mysql_import_command"]);
	}

	/**
	 * @param string $mysql_import_command
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlImportCommand(string $mysql_import_command)
	{
		$this->config["mysql_import_command"] = $mysql_import_command;
		return($this);
	}
}