<?php


namespace NEM\Infrastructure;

use NEM\NemSDK;

class Block {

	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}


}