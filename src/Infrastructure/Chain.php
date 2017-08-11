<?php


namespace evias\NEMBlockchain\Infrastructure;

use evias\NEMBlockchain\NemSDK;

class Chain {

	public $nemSDK;
	private $endpoint = "/chain/";

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}


	/**
	 * Gets the current height of the block chain.
	 * @returns Observable<BlockHeight>
	 */
	public function getBlockchainHeight() {
		return $this->nemSDK->api->getJSON( $this->endpoint . 'height', "" );
	}

	/**
	 * Gets the current score of the block chain. The higher the score, the better the chain.
	 * During synchronization, nodes try to get the best block chain in the network.
	 * @returns Observable<BlockChainScore>
	 */
	public function getBlockchainScore() {
		return $this->nemSDK->api->getJSON( $this->endpoint . 'score', "" );
	}

	/**
	 * Gets the current last block of the chain.
	 * @returns Observable<Block>
	 */
	public function getBlockchainLastBlock() {
		return $this->nemSDK->api->getJSON( $this->endpoint . 'last-block', "" );
	}

}