<?php
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
		"mysql_enabled" => false,
		"mysql_file" => ".asset-manager.sql",
		"mysql_dump_command" => "mysqldump -h {host} -u {user} -p{pass} {db} > {file}",
		"mysql_import_command" => "mysql -h {host} -u {user} -p{pass} {db} < {file}",
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
		"mysql_enable" => null,
		"mysql_host" => null,
		"mysql_port" => 3306,
		"mysql_user" => null,
		"mysql_pass" => null,
		"mysql_db" => null,
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
	 * @param $composerJson
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
	public function getRepostitory()
	{
		return($this->composerConfig["repository"]);
	}

	/**
	 * @param string $repository
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setRepostitory(string $repository)
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
	public function addPath(string $path)
	{
		$this->composerConfig["paths"][] = $path;
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
	public function setMethod(string $method)
	{
		$this->composerConfig["method"] = $method;
		return($this);
	}

	/**
	 * @return bool
	 */
	public function getMysqlEnabled()
	{
		return($this->composerConfig["mysql_enabled"]);
	}

	/**
	 * @param bool $mysql_enabled
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlEnabled(bool $mysql_enabled)
	{
		$this->composerConfig["mysql_enabled"] = $mysql_enabled;
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMysqlFile()
	{
		return($this->composerConfig["mysql_file"]);
	}

	/**
	 * @param string $mysql_file
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlFile(string $mysql_file)
	{
		$this->composerConfig["mysql_file"] = $mysql_file;
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMysqlDumpCommand()
	{
		return($this->composerConfig["mysql_dump_command"]);
	}

	/**
	 * @param string $mysql_dump_command
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlDumpCommand(string $mysql_dump_command)
	{
		$this->composerConfig["mysql_dump_command"] = $mysql_dump_command;
		return($this);
	}

	/**
	 * @return string
	 */
	public function getMysqlImportCommand()
	{
		return($this->composerConfig["mysql_import_command"]);
	}

	/**
	 * @param string $mysql_import_command
	 * @return \FSmoak\AssetManagerPlugin\Config
	 */
	public function setMysqlImportCommand(string $mysql_import_command)
	{
		$this->composerConfig["mysql_import_command"] = $mysql_import_command;
		return($this);
	}
	
	public function getEnviroment()
	{
		return($this->assetManagerConfig["environment"]);
	}
	
	public function setEnviroment($enviroment)
	{
		$this->assetManagerConfig["environment"] = $enviroment;
		return($this);
	}
}