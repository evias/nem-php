<?php

namespace NEM\Models;

use NEM\NemSDK;
use NEM\Models\Account\Account;
use NEM\Models\Account\Address;
use NEM\Models\Fee\Fee;
use NEM\Models\Mosaic\Mosaic;
use NEM\Models\Mosaic\Xem;
use NEM\Models\Namespaces\Namespaces;
use NEM\Models\Blockchain\Blockchain;
use NEM\Models\Transaction\Transaction;

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