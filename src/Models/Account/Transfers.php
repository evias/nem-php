<?php


namespace NEM\Models\Account;

use NEM\NemSDK;

class Transfers {

	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function incoming() {
		return json_decode( $this->nemSDK->api->getJSON( "/account/transfers/incoming?address=" . $this->nemSDK->address( $this->nemSDK->account()->address )->plain(), "" ) )->data;
	}

	public function all() {
		return json_decode( $this->nemSDK->api->getJSON( "/account/transfers/all?address=" . $this->nemSDK->address( $this->nemSDK->account()->address )->plain(), "" ) )->data;
	}

}