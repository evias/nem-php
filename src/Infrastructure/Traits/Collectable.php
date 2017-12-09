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
namespace NEM\Infrastructure;

trait Collectable
{
    public function history($method, $field)
    {
        $transactions = $this->$method($address);
        $lastHash     = (end($transactions))->meta->hash->data;
        $complete     = false;
        $count        = 1;
        while ( $complete === false && $count < ( $rows / 25 ) ) {
            $next = \NemSDK::account()->allTransactions( config( 'nem.nemventoryAddress' ), $lastHash );
            if ( count( $next ) < 1 ) {
                $complete = true;
            }
            $transactions = array_merge( $transactions, $next );
            $lastHash     = ( end( $transactions ) )->meta->hash->data;
            $count ++;
        }

        return $transactions;
    }