<?php


namespace evias\NEMBlockchain\Models\Namespaces;


use evias\NEMBlockchain\NemSDK;

class Namespaces {

	public $namespace;
	private $nemSDK;

	public function __construct( NemSDK $nemSDK, $namespace ) {
		$this->nemSDK    = $nemSDK;
		$this->namespace = $namespace;
	}

	public function getMosaics() {
		return $this->nemSDK->api->getJSON( '/namespace/mosaic/definition/page?namespace=' . $this->namespace, "" );
	}
}