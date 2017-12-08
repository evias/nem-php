<?php


namespace NEM\Models\Account;


use NEM\NemSDK;

class Account {

	public $nemSDK;
	public $address;
	public $publicKey;
	public $privateKey;

	public function __construct( NemSDK $nemSDK, $address = null ) {
		$this->nemSDK = $nemSDK;
	}

	public function generate() {
		$new_account      = $this->nemSDK->api->getJSON( "/account/generate", "" );
		$account          = json_decode( $new_account );
		$this->address    = $account->address;
		$this->publicKey  = $account->publicKey;
		$this->privateKey = $account->privateKey;

		return $this;
	}

	public function transfers() {
		return new Transfers( $this->nemSDK );
	}


}