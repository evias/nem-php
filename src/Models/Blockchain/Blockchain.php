<?php

namespace evias\NEMBlockchain\Models\Blockchain;


use evias\NEMBlockchain\NemSDK;

class Blockchain {
	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function height() {
		return $this->nemSDK->api->getJSON( "/chain/height", "" );
	}

	public function lastBlock() {
		return $this->nemSDK->api->getJSON( '/chain/last-block', "" );
	}

	public function nodeInfo() {
		return json_decode( $this->nemSDK->api->getJSON( '/node/info', "" ) );
	}


}