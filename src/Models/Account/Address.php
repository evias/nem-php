<?php
/**
 * Created by PhpStorm.
 * User: Eier
 * Date: 22.07.2017
 * Time: 20:53
 */

namespace evias\NEMBlockchain\Models\Account;

use evias\NEMBlockchain\NemSDK;

class Address {

	public $nemSDK;
	public $address;

	/**
	 * Address constructor.
	 *
	 * @param string $address
	 */
	public function __construct( NemSDK $nemSDK, $address = null ) {
		$this->nemSDK = $nemSDK;
		//It class is initated with address($address) it will return a plain address.
		$this->setAddress( $address );
	}

	public function plain( $address = null ) {
		$this->setAddress( $address );

		return strtoupper( preg_replace( "/[^a-zA-Z0-9]+/", "", $this->address ) );
	}

	public function pretty( $address = null ) {
		$this->setAddress( $address );

		return substr( chunk_split( $this->address, 5, '-' ), 0, - 1 );
	}

	public function transfersIncoming( $address = null ) {
		return $this->nemSDK->api->getJSON( "/account/transfers/incoming?address=" . $this->plain( $address ), "" );
	}


	/*Misc functions*/
	private function setAddress( $address ) {
		if ( $address !== null ) {
			//Todo: perform validation here.
			$this->address = $address;
		}
	}


}