<?php


namespace NEM\Models\Mosaic;

use NEM\NemSDK;

class Mosaic {

	public $nemSDK;


	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function properties( $properties = array() ) {
		$defaults = [
			'divisibility'  => 0,
			'initialSupply' => 1000,
			'supplyMutable' => true,
			'transferable'  => true,
		];
		$p        = array_merge( $defaults, $properties );

		return ( new MosaicProperties( $p['divisibility'], $p['initialSupply'], $p['supplyMutable'], $p['transferable'] ) )->toDTO();
	}

	public function getProperties( $namespace, $mosaic ) {
		$mosaics = json_decode( $this->nemSDK->api->getJSON( "/namespace/mosaic/definition/page?namespace=" . $namespace, "" ) )->data;
		if ( is_array( $mosaics ) ) {
			foreach ( $mosaics as $key => $value ) {
				if ( $mosaic === $value->mosaic->id->name ) {
					return [
						'divisibility'  => intval( $value->mosaic->properties[0]->value ),
						'initialSupply' => intval( $value->mosaic->properties[1]->value ),
						'supplyMutable' => $value->mosaic->properties[2]->value === "true" ? true : false,
						'transferable'  => $value->mosaic->properties[3]->value === "true" ? true : false,
					];
				}
			}
		}

		return false;
	}

	public function mosaicId( $namespace, $mosaic ) {
		return new MosaicId( $namespace, $mosaic );
	}

	public function mosaicLevy( $properties = array() ) {
		if ( empty( $properties ) ) {
			return null;
		}
		$namespace = ( array_key_exists( 'namespace', $properties ) ) ? $properties['namespace'] : 'nem';
		$mosaic    = ( array_key_exists( 'mosaic', $properties ) ) ? $properties['mosaic'] : 'xem';

		$defaults = [
			'type'      => 1,
			'recipient' => null,
			'fee'       => null,
			'mosaicId'  => $this->mosaicId( $namespace, $mosaic )->toDTO(),
		];
		$p        = array_merge( $defaults, $properties );

		return ( new MosaicLevy( $p['type'], $p['recipient'], $p['fee'], $p['mosaicId'] ) )->toDTO();
	}


}