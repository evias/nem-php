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
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Infrastructure;

use NEM\Models\Transaction as TxModel;
use NEM\Core\KeyPair;
use NEM\Core\Serializer;

/**
 * This is the Transaction Infrastructure service
 *
 * This service implements API endpoints of the NEM
 * Infrastructure.
 * 
 * @internal This class is currently *not* unit tested.
 *           Parts of this class may be malfunctioning or 
 *           not working all.
 */
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
     * WARNING: In case you don't provide this method with a KeyPair, 
     * you must provide a `privateKey` in the transaction attributes. 
     * It is *not* recommended to send `privateKey` data over any network,
     * even local.
     * 
     * @param   \NEM\Models\Transaction     $transaction
     * @return  \NEM\Models\Transaction
     */
    public function announce(TxModel $transaction, KeyPair $kp = null)
    {
        // set optional `signer` in case we provide a KeyPair
        if (null !== $kp) {
            $transaction->setAttribute("signer", $kp->publicKey);
        }

        // now we can serialize and sign
        $serialized = $transaction->serialize();
        $signature  = null !== $kp ? $kp->sign($serialized) : null;
        $broadcast  = [];

        if ($signature) {
            // valid KeyPair provided, signature was created.

            // recommended signed transaction broadcast method.
            $endpoint  = "transaction/announce";
            $broadcast = [
                "data" => $serialized,
                "signature" => $signature,
            ];
        }
        else {
            // WARNING: with this you must provide a `privateKey`
            // in the transaction attributes. This is *not* recommended.
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
