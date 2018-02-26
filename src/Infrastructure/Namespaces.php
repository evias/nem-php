<?php


namespace NEM\Infrastructure;

use NEM\NemSDK;

/**
 * This is the Namespace Infrastructure service
 *
 * This service implements API endpoints of the NEM
 * Infrastructure.
 * 
 * @internal This class is currently *not* unit tested.
 *           Parts of this class may be malfunctioning or 
 *           not working all.
 */
class Namespaces {

	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

}