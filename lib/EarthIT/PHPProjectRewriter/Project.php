<?php

class EarthIT_PHPProjectRewriter_Project
{
	public static function loadConfig($file) {
		if( !file_exists($file) ) {
			throw new Exception("$file not found");
		}
		$json = file_get_contents($file);
		if( $json === false ) {
			throw new Exception("Failed to read $file");
		}
		$config = json_decode($json, true);
		if( $config === null ) {
			throw new Exception("Failed to parse JSON from $file");
		}
		return $config;
	}
	
	protected $dir;
	protected $config;
	
	public function __construct( $dir, $config=null ) {
		$this->dir = $dir;
		$this->config = $config;
	}
	
	public function getDir() { return $this->dir; }
	public function getConfig() {
		if( !isset($this->config) ) {
			// Attempt to load it...
			$this->config = self::loadConfig("{$this->dir}/.ppi-settings.json");
		}
		return $this->config;
	}
	
	/**
	 * Returns the namespace as a filesystem path fragment, e.g.
	 * if the project namespace is "My_CoolProject", this would return
	 * "My/CoolProject".
	 */
	public function getKrog13() {
		return str_replace(array('_','\\'),'/',$this->config['phpNamespace']);
	}
}
