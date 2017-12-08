<?php


namespace NEM\Infrastructure;


use NEM\Models\Transaction\Multisig;
use NEM\Models\Transaction\Transfer;
use NEM\Models\Transaction\Mosaic;
use NEM\NemSDK;

class Transaction {
	public $nemSDK;
	private $multisig = false;
	private $transaction;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	/*Multisig*/
	public function multisig( $multisigPublicKey, $multisigPrivateKey ) {
		$this->transaction = new Multisig( $this->nemSDK, $multisigPublicKey, $multisigPrivateKey );
		$this->multisig    = true;

		return $this;
	}


	public function transfer( $recipient, $amount, $message, $senderPublicKey, $senderPrivateKey = null, $mosaics = array(), $fee = "auto", $encrypted = false ) {
		if ( $this->multisig ) {
			$this->transaction->setOtherTrans( ( new Transfer( $this->nemSDK, $recipient, $amount, $message, $senderPublicKey, $this->transaction->multisigPrivateKey, $fee, $encrypted, $mosaics ) )->toDTO() );
		} else {
			$this->transaction = new Transfer( $this->nemSDK, $recipient, $amount, $message, $senderPublicKey, $senderPrivateKey, $fee, $encrypted, $mosaics );
		}

		return $this->sendTransaction();
	}

	/**
	 * @param       $mosaicName
	 * @param       $mosaicDescription
	 * @param       $namespaceId
	 * @param       $signerPublicKey
	 * @param       $signerPrivateKey
	 * @param array $properties = [
	 *                          'divisibility' => 2, //default 0
	 *                          'initialSupply' => 5000, //default 1000
	 *                          'supplyMutable' => true, //default  true
	 *                          'transferable' => false, //default true
	 *                          ]
	 *
	 * @param array $levy       = [
	 *                          'type' => 1, // 1 is fixed fee, 2 is % fee. Default 1
	 *                          'recipient' => "TCRE2YAZ7DWF4WL5BU3VPZ2ZWFHD6WGIKRG26IZO", //No default
	 *                          'fee' => 10, //No default
	 *                          "namespace" => 'snap', //default nem
	 *                          'mosaic' => 'coin' //default xem
	 *                          ]
	 *
	 * @return Mosaic
	 */
	public function mosaic( $mosaicName, $mosaicDescription, $namespaceId, $signerPublicKey, $signerPrivateKey = null, $properties = array(), $levy = array() ) {
		if ( $this->multisig ) {
			$this->transaction->setOtherTrans( ( new Mosaic( $this->nemSDK, $mosaicName, $mosaicDescription, $namespaceId, $signerPublicKey, $this->transaction->multisigPrivateKey, $properties, $levy ) )->toDTO() );
		} else {
			$this->transaction = new Mosaic( $this->nemSDK, $mosaicName, $mosaicDescription, $namespaceId, $signerPublicKey, $signerPrivateKey, $properties, $levy );
		}

		return $this->sendTransaction();
	}

	private function sendTransaction() {
		return json_decode( $this->nemSDK->api->post( '/transaction/prepare-announce', $this->transaction->toDTO() ) );
	}

}