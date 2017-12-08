<?php

namespace NEM\Models\Transaction;


use NEM\NemSDK;

class Transaction {

	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function transactionsTypes() {
		return new TransactionTypes();
	}

	public function message( $message = null, $type = 1 ) {
		return new Message( $message, $type );
	}

	public function timeWindow() {
		return new TimeWindow( $this->nemSDK );
	}

	public function multisig( $multisigPublicKey, $multisigPrivateKey ) {
		return new Multisig( $this->nemSDK, $multisigPublicKey, $multisigPrivateKey );
	}

	public function transfer( $recipient, $amount, $message, $senderPublicKey, $senderPrivateKey, $mosaics = array(), $fee = "auto", $encrypted = false ) {
		return new Transfer( $this->nemSDK, $recipient, $amount, $message, $senderPublicKey, $senderPrivateKey, $fee, $encrypted, $mosaics );
	}


	/**
	 * @param       $mosaicName
	 * @param       $mosaicDescription
	 * @param       $namespaceId
	 * @param       $signerPublicKey
	 * @param       $signerPrivateKey
	 * @param array $properties //defaults empty
	 *                          [
	 *                          'divisibility' => 2, //default 0
	 *                          'initialSupply' => 5000, //default 1000
	 *                          'supplyMutable' => true, //default  true
	 *                          'transferable' => false, //default true
	 *                          ],
	 *
	 * @param array $levy       //defaults empty
	 *                          [
	 *                          'type' => 1, // 1 is fixed fee, 2 is % fee. Default 1
	 *                          'recipient' => "TCRE2YAZ7DWF4WL5BU3VPZ2ZWFHD6WGIKRG26IZO", //No default
	 *                          'fee' => 10, //No default
	 *                          "namespace" => 'snap', //default nem
	 *                          'mosaic' => 'coin' //default xem
	 *                          ]
	 *
	 * @return Mosaic
	 */
	public function mosaic( $mosaicName, $mosaicDescription, $namespaceId, $signerPublicKey, $signerPrivateKey, $properties = array(), $levy = array() ) {
		return new Mosaic( $this->nemSDK, $mosaicName, $mosaicDescription, $namespaceId, $signerPublicKey, $signerPrivateKey, $properties, $levy );
	}

}