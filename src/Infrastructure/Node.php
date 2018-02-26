<?php


namespace NEM\Infrastructure;

use NEM\NemSDK;

/**
 * This is the Node Infrastructure service
 *
 * This service implements API endpoints of the NEM
 * Infrastructure.
 * 
 * @internal This class is currently *not* unit tested.
 *           Parts of this class may be malfunctioning or 
 *           not working all.
 */
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