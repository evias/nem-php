<?php

namespace evias\NEMBlockchain\Models\Transaction;

use evias\NEMBlockchain\NemSDK;

class TimeWindow {

	public $nemSDK;
	private $timestampNemesisBlock = 1427587585;
	private $deadline = 60 * 5; //5 minutes


	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	/**
	 * Provide a unix timestamp and will give amount of seconds since timestamp.
	 *
	 * @param $timestamp
	 *
	 * @return int
	 */
	private function secondsUntilNow( $timestamp ) {
		$dtF = new \DateTime( 'NOW' );
		$dtT = new \DateTime( "@$timestamp" );

		return $dtF->getTimestamp() - $dtT->getTimestamp();
	}

	/**
	 * Returns timestamp since NEM Nemesis block.
	 *
	 * @return int
	 */
	public function timestamp() {
		return $this->secondsUntilNow( $this->timestampNemesisBlock );
	}

	/**
	 * Returns timestamp since NEM Nemesis block with fixed deadline added.
	 *
	 * @return int
	 */
	public function deadline() {
		return $this->deadline + $this->secondsUntilNow( $this->timestampNemesisBlock );
	}


}