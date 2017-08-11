<?php


namespace evias\NEMBlockchain\Infrastructure;

use evias\NEMBlockchain\NemSDK;

class Namespaces {

	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

}