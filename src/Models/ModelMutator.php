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

use NEM\NemSDK;
use NEM\Models\Account\Account;
use NEM\Models\Account\Address;
use NEM\Models\Fee\Fee;
use NEM\Models\Mosaic\Mosaic;
use NEM\Models\Mosaic\Xem;
use NEM\Models\Namespaces\Namespaces;
use NEM\Models\Blockchain\Blockchain;
use NEM\Models\Transaction\Transaction;

class ModelMutator
{
    /**
     * This __call hook makes sure calls to the Mutator object
     * will always instantiate a Models class provided by the SDK.
     *
     * @example Example calls for \NEM\Models\ModelMutator
     *
     * $sdk = new SDK();
     * $sdk->models()->address(["address" => "NB72EM6TTSX72O47T3GQFL345AB5WYKIDODKPPYW"]); // will automatically craft a \NEM\Models\Address object
     * $sdk->models()->namespace(["namespace" => "evias"]); // will automatically craft a \NEM\Models\Namespace object
     *
     * @example Example building \NEM\Models\Model objects with the ModelMutator
     *
     * $sdk = new SDK();
     * $addr = $sdk->models()->address();
     * $addr->address = "NB72EM6TTSX72O47T3GQFL345AB5WYKIDODKPPYW";
     * var_dump($addr->toDTO()); // will contain address field
     *
     * @param  [type] $method    [description]
     * @param  array  $arguments [description]
     * @return [type]            [description]
     */
    public function __call($method, array $arguments)
    {
        if (method_exists($this, $method))
            // method overload exists, call it.
            return call_user_func_array([$this, $method], $arguments);

        // method does not exist, try to craft model class instance.

        // snake_case to camelCase
        $normalized = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $input)), '_');
        $className  = ucfirst($normalized);
        $modelClass = "\\NEM\\Models\\" . $className;

        if (!class_exists($modelClass)) {
            throw new BadMethodCallException("Model class '" . $infraClass . "' could not be found in \\NEM\\Model namespace.");
        }

        //XXX add fields list to Models
        $instance = new $modelClass($arguments);
        return $instance;
    }


	public function address( $address = null ) {
		return new Address( $this->nemSDK, $address );
	}

	public function xem( $amount = null ) {
		return new Xem( $this->nemSDK, $amount );
	}

	public function namespaces( $namespace = null ) {
		return new Namespaces( $this->nemSDK, $namespace );
	}

	public function blockchain() {
		return new Blockchain( $this->nemSDK );
	}

	public function mosaic() {
		return new Mosaic( $this->nemSDK );
	}

	public function account( $account = null ) {
		return new Account( $this->nemSDK, $account );
	}

	public function transaction() {
		return new Transaction( $this->nemSDK );
	}

	public function fee() {
		return new Fee( $this->nemSDK );
	}

}