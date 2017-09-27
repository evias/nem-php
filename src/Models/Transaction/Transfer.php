<?php


namespace evias\NEMBlockchain\Models\Transaction;

use evias\NEMBlockchain\NemSDK;

class Transfer {

	public $nemSDK;
	private $recipient;
	private $amount;
	private $message;
	private $senderPublicKey;
	private $senderPrivateKey;
	private $fee;
	private $mosaics;

	/**
	 * Transfer constructor.
	 *
	 * @param $recipient
	 * @param $amount
	 * @param $message
	 * @param $senderPublicKey
	 * @param $senderPrivateKey
	 * @param $fee
	 */
	public function __construct( NemSDK $nemSDK, $recipient, $amount, $message, $senderPublicKey, $senderPrivateKey, $fee, $encrypted, $mosaics ) {
		$this->nemSDK           = $nemSDK;
		$this->recipient        = $this->nemSDK->models()->address( $recipient )->plain();
		$this->amount           = intval( $amount );
		$this->message          = $message;
		$this->senderPublicKey  = $senderPublicKey;
		$this->senderPrivateKey = $senderPrivateKey;
		$this->fee              = 0;
		$this->feeStategy       = $fee;
		$this->encrypted        = $encrypted;
		$this->mosaics          = $mosaics;

		//Because amount is multiplier when sending mosaics, we need to ensure this is set to 1
		if ( ! empty( $this->mosaics ) ) {
			$this->amount = (integer) 1000000;
		} else {
			//Check that the amount is microXEM
			$this->nemSDK->models()->xem()->isMicroXem( $this->amount );
		}
		$this->setFee();
	}

	private function setFee() {

		switch ( true ) {
			case $this->feeStategy === "auto":
				$this->fee = $this->nemSDK->models()->fee()->transfer( $this->amount, $this->message, $this->mosaics );
				break;
			case $this->feeStategy === "high":
				$this->fee = $this->nemSDK->models()->fee()->transfer( $this->amount, $this->message, $this->mosaics ) + 500000;
				break;
			case is_numeric( $this->feeStategy ):
				$this->fee = intval( $this->nemSDK->models()->xem()->isMicroXem( $this->feeStategy ) );
				break;
			default:
				throw new \Exception( "Fee parameter not valid" );
		}
	}

	private function getVersion() {
		if ( ! empty( $this->mosaics ) ) {
			return $this->nemSDK->network()->getVersion( null, 2 );
		}

		return $this->nemSDK->network()->getVersion();
	}

	public function toDTO() {
		$dto = [
			"transaction" => [
				"timeStamp" => $this->nemSDK->models()->transaction()->timeWindow()->timestamp(),
				"amount"    => $this->amount,
				"fee"       => $this->fee,
				"recipient" => $this->recipient,
				"type"      => $this->nemSDK->models()->transaction()->transactionsTypes()::TRANSFER,
				"deadline"  => $this->nemSDK->models()->transaction()->timeWindow()->deadline(),
				"message"   => $this->nemSDK->models()->transaction()->message( $this->message )->toDTO(),
				"version"   => $this->getVersion(),
				"signer"    => $this->senderPublicKey,
			],
			"privateKey"  => $this->senderPrivateKey,
		];

		if ( ! empty( $this->mosaics ) ) {
			$dto['transaction']['mosaics'] = array();
			foreach ( $this->mosaics as $mosaic ) {
				array_push( $dto['transaction']['mosaics'], array(
					"mosaicId" => $this->nemSDK->models()->mosaic()->mosaicId( $mosaic['namespace'], $mosaic['mosaic'] )->toDTO(),
					"quantity" => intval( $mosaic['quantity'] ),
				) );
			}
		}

		return $dto;
	}

	public function create() {
		return json_decode( $this->nemSDK->api->post( '/transaction/prepare-announce', $this->toDTO() ) );
	}


}