<?php
/**
 * Created by PhpStorm.
 * User: Eier
 * Date: 21.07.2017
 * Time: 19:10
 */

namespace evias\NEMBlockchain;

use evias\NEMBlockchain\Infrastructure\Account;
use evias\NEMBlockchain\Infrastructure\Block;
use evias\NEMBlockchain\Infrastructure\Mosaic;
use evias\NEMBlockchain\Infrastructure\Namespaces;
use evias\NEMBlockchain\Infrastructure\Network;
use evias\NEMBlockchain\Infrastructure\Node;
use evias\NEMBlockchain\Infrastructure\Transaction;
use evias\NEMBlockchain\Models\Models;

class NemSDK {

	public $api;

	/**
	 * NemSDK constructor.
	 *
	 * @param array $options [
	 *                       "protocol" => "http",
	 *                       "use_ssl" => false,
	 *                       "host"      => "go.nem.ninja",
	 *                       "port"    => 7890,
	 *                       "endpoint" => "/",
	 *                       ]
	 */
	public function __construct( $options = array() ) {
		if ( ! empty( $options ) ) {
			$this->setOptions( $options );
		} else {
			$this->api = \App::make( 'nem' );
		}
	}

	public function setOptions( $options ) {
		$this->api = new API();
		$this->api->setOptions( $options );
	}


	public function models() {
		return new Models( $this );
	}

	/*Infrastructure endpoints*/

	public function network() {
		return new Network( $this );
	}

	public function account() {
		return new Account( $this );
	}

	public function block() {
		return new Block( $this );
	}

	public function mosaic() {
		return new Mosaic( $this );
	}

	public function namespaces() {
		return new Namespaces( $this );
	}

	public function node() {
		return new Node( $this );
	}

	public function transaction() {
		return new Transaction( $this );
	}


}