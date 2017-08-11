<?php


namespace evias\NEMBlockchain\Models\Transaction;


class Message {

	private $message;
	private $type;

	public function __construct( $message = null, $type = null ) {
		$this->message = $message;
		$this->type    = $type;

	}

	public function strToHex( $string ) {
		$hex = '';
		for ( $i = 0; $i < strlen( $string ); $i ++ ) {
			$ord     = ord( $string[ $i ] );
			$hexCode = dechex( $ord );
			$hex     .= substr( '0' . $hexCode, - 2 );
		}

		return strToUpper( $hex );
	}

	public function hexToStr( $hex ) {
		$string = '';
		for ( $i = 0; $i < strlen( $hex ) - 1; $i += 2 ) {
			$string .= chr( hexdec( $hex[ $i ] . $hex[ $i + 1 ] ) );
		}

		return $string;
	}

	public function toDTO() {
		return [
			"payload" => $this->strToHex( $this->message ),
			"type"    => $this->type,
		];
	}

	/**
	 * TRYS to evaluate if the message is hex
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	private function isHex( $string ) {
		if ( ctype_xdigit( $string ) ) {
			return true;
		}

		return false;
	}

}