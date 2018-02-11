<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Infrastructure;

use NEM\Models\Transaction as TxModel;
use NEM\Core\KeyPair;
use NEM\Core\Serializer;

class Transaction 
    extends Service
{
    /**
     * The Base URL for this endpoint.
     *
     * @var string
     */
    protected $baseUrl = "/transaction";

    /**
     * Announce a transaction. The transaction will be serialized before
     * it is sent to the server. 
     * 
     * Additionally, a KeyPair object can be passed to this method *if you wish 
     * to have the transaction **signed locally** by the SDK instead of letting
     * the remote NIS sign for you*. The local signing method **is recommended**.
     * 
     * @param   \NEM\Models\Transaction     $transaction
     * @return  \NEM\Models\Transaction
     */
    public function announce(TxModel $transaction, KeyPair $kp = null)
    {
        $serializer = new Serializer();
        $serialized = $serializer->serialize($transaction);
        $signature  = null !== $kp ? $kp->sign($serialized) : null;
        $broadcast  = [];

        if ($signature) {
            $endpoint  = "transaction/announce";
            $broadcast = [
                "data" => $serialized,
                "signature" => $signature,
            ];
        }
        else {
            $endpoint  = "transaction/prepare-announce";
            $broadcast = $transaction->toDTO();
        }

        $apiUrl = $this->getPath($endpoint, []);
        $response = $this->api->post($apiUrl, $params);

        //XXX include Error checks
        $object = json_decode($response);
        return $this->createBaseModel($object); //XXX brr => error/content validation first
    }

    /**
     * Gets a transaction meta data pair where the transaction hash corresponds
     * to the said `hash` parameter.
     */
    public function byHash($hash)
    {
        $params = ["hash" => $hash];
        $apiUrl = $this->getPath('transfers/incoming', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createTransactionModel($object['data']); //XXX brr => error/content validation first
    }
}
