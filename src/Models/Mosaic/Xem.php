<?php
/**
 * Created by PhpStorm.
 * User: Eier
 * Date: 22.07.2017
 * Time: 22:19
 */

namespace evias\NEMBlockchain\Models\Mosaic;


use evias\NEMBlockchain\NemSDK;

class Xem {

	public $nemSDK;
	public $amount;
	public $xemFactor = 1000000;

	public function __construct( NemSDK $nemSDK, $amount = null ) {
		$this->nemSDK = $nemSDK;
		$this->setAmount( $amount );
	}

	public function toMicroxem( $amount = null ) {
		$this->setAmount( $amount );
		if ( is_array( $this->amount ) ) {
			$amount_array = [];
			foreach ( $this->amount as $key => $value ) {
				$amount_array[ $key ] = $value * $this->xemFactor;
			}

			return $amount_array;
		}

		return (integer) $this->amount * $this->xemFactor;
	}

	public function fromMicroxem( $amount = null ) {
		$this->setAmount( $amount );

		return $this->amount / $this->xemFactor;
	}

	/*Misc functions*/
	private function setAmount( $amount = null ) {
		if ( $amount !== null ) {
			//Todo: perform validation here.
			$this->amount = $amount;
		}
	}

	public function isMicroXem( $amount = null ) {
		$this->setAmount( $amount );
		if ( ( strlen( $this->amount ) > 6 && strpos( $this->amount, ',' ) === false && strpos( $this->amount, '.' ) === false ) || $this->amount === 0 ) {
			return (integer) $amount;
		}
		throw new \Exception( "Is not Microxem" );
	}

}