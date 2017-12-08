<?php


namespace NEM\Infrastructure;

use NEM\NemSDK;

class Network {

	public $nemSDK;
	public $version;
	public $networkType;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function getVersion( $identifier = null, $version = 1 ) {
		if ( ! empty( $this->version[ $version ] ) ) {
			return $this->version[ $version ];
		}
		$this->checkNetwork( $identifier );

		return $this->version[ $version ];
	}

	public function getNetworkType( $identifier = null ) {
		if ( ! empty( $this->networkType ) ) {
			return $this->networkType;
		}
		$this->checkNetwork( $identifier );

		return $this->networkType;
	}

	private function checkNetwork( $identifier = null ) {
		if ( preg_match( '/^([A-Z]{1})[A-Z0-9-]{39,45}$/', strtoupper( $identifier ), $matches ) ) {
			if ( $matches[1] === "N" ) {
				$this->setNetworkType( "MAINNET" );
			} elseif ( $matches[1] === "T" ) {
				$this->setNetworkType( "TESTNET" );
			} else {
				throw new \Exception( "Could not indentify Nem Network type from identifier. Assumed string and address." );
			}
		} else {
			$networkId = ( $this->nemSDK->node()->info() )->metaData->networkId;
			if ( $networkId === 104 ) {
				$this->setNetworkType( "main" );
			} elseif ( $networkId === - 104 ) {
				$this->setNetworkType( "test" );
			} else {
				throw new \Exception( "Could not indentify Nem Network type from calling the node. Assumed nothing as identfier" );
			}
		}
	}

	private function setNetworkType( $type ) {
		switch ( true ) {
			case stripos( $type, "main" ) !== false:
				$this->version     = [
					1 => 1744830465,
					2 => 1744830466,
				];
				$this->networkType = "MAINNET";
				break;
			case stripos( $type, "test" ) !== false:
				$this->version     = [
					'1' => - 1744830463,
					'2' => - 1744830462,
				];
				$this->networkType = "TESTNET";
				break;
			default:
				throw new \Exception( "Could not setNetworkType in NemSDK " );
		}
	}

}