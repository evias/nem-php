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
 * @version    0.1.0
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM\Models;

class Account
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "address",
        "publicKey",
        "privateKey"
    ];

	public function __construct( NemSDK $nemSDK, $address = null ) {
		$this->nemSDK = $nemSDK;
	}

	public function generate() {
		$new_account      = $this->nemSDK->api->getJSON( "/account/generate", "" );
		$account          = json_decode( $new_account );
		$this->address    = $account->address;
		$this->publicKey  = $account->publicKey;
		$this->privateKey = $account->privateKey;

		return $this;
	}

	public function transfers() {
		return new Transfers( $this->nemSDK );
	}


}