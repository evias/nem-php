<?php


namespace evias\NEMBlockchain\Infrastructure;

use evias\NEMBlockchain\NemSDK;

class Block {

	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}


}