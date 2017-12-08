<?php
/**
 * Part of the evias/php-nem-laravel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/php-nem-laravel
 * @version    0.0.2
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM;

use NEM\Infrastructure\Account;
use NEM\Infrastructure\Block;
use NEM\Infrastructure\Mosaic;
use NEM\Infrastructure\Namespaces;
use NEM\Infrastructure\Network;
use NEM\Infrastructure\Node;
use NEM\Infrastructure\Transaction;
use NEM\Models\Models;

class SDK 
{

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