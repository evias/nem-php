<?php

namespace evias\NEMBlockchain\Models\Fee;

use evias\NEMBlockchain\NemSDK;

class Fee {

	public $nemSDK;

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	public function transfer( $amount, $message = null, $mosaics = null ) {
		$fee = 0;

		$fee = floor( 0.05 * $this->calculateMinimum( $amount / 1000000 ) * 1000000 );

		if ( strlen( $message ) != 0 ) {
			$fee += 0.05 * ( floor( ( strlen( $message ) / 2 ) / 32 ) + 1 ) * 1000000;
		}

		if ( ! empty( $mosaics ) ) {
			$mosaicFee = 0;
			foreach ( $mosaics as $mosaic ) {

				if ( $mosaic['quantity'] > 10000 ) {
					//custom mosaic fee, Max is 1.25.
					$mosaicProperties        = $this->nemSDK->models()->mosaic()->getProperties( $mosaic['namespace'], $mosaic['mosaic'] );
					$maxMosaicQuantity       = 9000000000000000;
					$totalMosaicQuantity     = $mosaicProperties['initialSupply'] * ( 10 ^ $mosaicProperties['divisibility'] );
					$supplyRelatedAdjustment = floor( 0.8 * ( log( $maxMosaicQuantity / $totalMosaicQuantity ) ) );
					$xemfee                  = min( 25, ( $mosaic['quantity'] * 900000 ) / $mosaicProperties['initialSupply'] );
					$mosaicFee               += ( 0.05 * max( 1, $xemfee - $supplyRelatedAdjustment ) ) * 1000000;
				} else {
					//Small Business mosaic fee
					$mosaicFee += 0.05 * 1000000;
				}
			}
			$fee += $mosaicFee;
		}

		return (integer) $fee;
	}

	public function multisig() {
		return (integer) floor( 3 * 0.05 * 1000000 );
	}

	public function mosaic() {
		return (integer) floor( 3 * 0.05 * 1000000 );
	}

	private function calculateMinimum( $amount ) {
		$amount = floor( max( 1, $amount / 10000 ) );

		return (integer) $amount > 25 ? 25 : $amount;
	}

}