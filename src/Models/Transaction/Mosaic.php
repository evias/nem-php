<?php


namespace evias\NEMBlockchain\Models\Transaction;

use evias\NEMBlockchain\NemSDK;

class Mosaic {

	public $nemSDK;
	private $creationFeeSinkTEST_NET = "TBMOSAICOD4F54EE5CDMR23CCBGOAM2XSJBR5OLC";
	private $creationFeeSinkMAIN_NET = "NBMOSAICOD4F54EE5CDMR23CCBGOAM2XSIUX6TRS";
	private $creationFee = 10000000;
	private $name;
	private $description;
	private $namespaceId;
	private $signerPublicKey;
	private $signerPrivateKey;
	private $properties;
	private $levy;


	/**
	 * Mosaic constructor.
	 *
	 * @param $nemSDK
	 * @param $mosaicName
	 * @param $mosaicDescription
	 * @param $namespaceId
	 * @param $signerPublicKey
	 * @param $signerPrivateKey
	 * @param $properties
	 * @param $levy
	 */
	public function __construct( NemSDK $nemSDK, $mosaicName, $mosaicDescription, $namespaceId, $signerPublicKey, $signerPrivateKey, $properties, $levy ) {
		$this->nemSDK           = $nemSDK;
		$this->name             = $mosaicName;
		$this->description      = $mosaicDescription;
		$this->namespaceId      = $namespaceId;
		$this->signerPublicKey  = $signerPublicKey;
		$this->signerPrivateKey = $signerPrivateKey;
		$this->properties       = $properties;
		$this->levy             = $levy;
	}


	private function getCreationFeeSink() {
		switch ( $this->nemSDK->network()->getNetworkType() ) {
			case "MAINNET" :
				return $this->creationFeeSinkMAIN_NET;
			case "TESTNET":
				return $this->creationFeeSinkTEST_NET;
			default:
				throw new \Exception( "Could not get Network type too set CreationFeeSink for Mosaic." );
		}
	}

	public function toDTO() {
		$dto = [
			"transaction" => [
				"timeStamp"        => $this->nemSDK->models()->transaction()->timeWindow()->timestamp(),
				"fee"              => $this->nemSDK->models()->fee()->mosaic(),
				"type"             => $this->nemSDK->models()->transaction()->transactionsTypes()::MOSAIC_DEFINITION_CREATION,
				"deadline"         => $this->nemSDK->models()->transaction()->timeWindow()->deadline(),
				"version"          => $this->nemSDK->network()->getVersion(),
				"signer"           => $this->signerPublicKey,
				"creationFee"      => $this->creationFee,
				"creationFeeSink"  => $this->getCreationFeeSink(),
				"mosaicDefinition" => [
					"creator"     => $this->signerPublicKey,
					"description" => $this->description,
					"id"          => [
						"namespaceId" => $this->namespaceId,
						"name"        => $this->name,
					],
					"properties"  => $this->nemSDK->models()->mosaic()->properties( $this->properties ),
				],
			],
			"privateKey"  => $this->signerPrivateKey,
		];
		//Levy is optional, so only add it if set.
		if ( ! empty( $this->levy ) ) {
			$dto['transaction']['mosaicDefinition']['levy'] = $this->nemSDK->models()->mosaic()->mosaicLevy( $this->levy );
		}
		$test = json_encode( $dto );

		return $dto;

	}

	public function create() {
		return json_decode( $this->nemSDK->api->post( '/transaction/prepare-announce', $this->toDTO() ) );
	}

}