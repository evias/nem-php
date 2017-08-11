<?php

namespace evias\NEMBlockchain\Models;

use evias\NEMBlockchain\NemSDK;
use evias\NEMBlockchain\Models\Account\Account;
use evias\NEMBlockchain\Models\Account\Address;
use evias\NEMBlockchain\Models\Fee\Fee;
use evias\NEMBlockchain\Models\Mosaic\Mosaic;
use evias\NEMBlockchain\Models\Mosaic\Xem;
use evias\NEMBlockchain\Models\Namespaces\Namespaces;
use evias\NEMBlockchain\Models\Blockchain\Blockchain;
use evias\NEMBlockchain\Models\Transaction\Transaction;

class Models {
	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function address( $address = null ) {
		return new Address( $this->nemSDK, $address );
	}

	public function xem( $amount = null ) {
		return new Xem( $this->nemSDK, $amount );
	}

	public function namespaces( $namespace = null ) {
		return new Namespaces( $this->nemSDK, $namespace );
	}

	public function blockchain() {
		return new Blockchain( $this->nemSDK );
	}

	public function mosaic() {
		return new Mosaic( $this->nemSDK );
	}

	public function account( $account = null ) {
		return new Account( $this->nemSDK, $account );
	}

	public function transaction() {
		return new Transaction( $this->nemSDK );
	}

	public function fee() {
		return new Fee( $this->nemSDK );
	}

}