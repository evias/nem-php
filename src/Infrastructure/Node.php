<?php


namespace NEM\Infrastructure;

use NEM\NemSDK;

class Node {

	public $nemSDK;
	public $endpoint = '/node/';

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function info() {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'info', "" ) );
	}


}