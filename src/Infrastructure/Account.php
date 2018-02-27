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

/**
 * This is the Account Infrastructure service
 *
 * This service implements API endpoints of the NEM
 * Infrastructure.
 * 
 * @internal This class is currently *not* unit tested.
 *           Parts of this class may be malfunctioning or 
 *           not working all.
 */
class Account
    extends Service
{
    /**
     * The Base URL for this endpoint.
     *
     * @var string
     */
    protected $baseUrl = "/account";

    /**
     * Let NIS generate an account KeyPair.
     * 
     * WARNING: This method can only be used on a locally running
     *          NIS. It is preferred to use the \NEM\Core\KeyPair
     *          class to create accounts on your machine rather than
     *          let NIS create an account for you.
     *
     * @internal This method only works with a locally running NIS.
     * @return  object      Object with keys `address`, `publicKey` and `privateKey`.
     */
    public function generateAccount()
    {
        $params = [];
        $apiUrl = $this->getPath('generate', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createAccountModel($object); //XXX brr => error/content validation first
    }

    /**
     * Gets an [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) for an account
     * by its Base32 address representation (T-, N-, M- prefixed addresses).
     *
     * @param   string  $address    Base32 representation of the account address (T-, N-, M- prefixed addresses).
     * @return  object              Instance with keys from [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) objects.
     */
    public function getFromAddress($address)
    {
        $apiUrl = $this->getPath('get', ["address" => $address]);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createAccountModel($object);
    }

    /**
     * Gets an [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) for an account
     * by its public key hexadecimal representation.
     *
     * @param   string  $publicKey  Hexadecimal representation of the Public Key
     * @return  object              Instance with keys from [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) objects.
     */
    public function getFromPublicKey($publicKey)
    {
        $apiUrl = $this->getPath('get/from-public-key', ["publicKey" => $publicKey]);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createAccountModel($object);
    }

    /**
     * Given a delegate (formerly known as remote) account's address, gets the [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) for the account for
     * which the given account is the delegate account. If the given account address is not a delegate account for any
     * account, the request returns the [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) for the given address.
     *
     * @param   string  $address    Base32 representation of the account address (T-, N-, M- prefixed addresses).
     * @return  object              Instance with keys from [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) objects.
     */
    public function getFromDelegatedAddress($address)
    {
        $apiUrl = $this->getPath('get/forwarded', ["address" => $address]);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createAccountModel($object);
    }

    /**
     * Retrieve the original account data by providing the public key of the delegate account.
     *
     * @param   string  $publicKey  Hexadecimal representation of the Public Key
     * @return  object              Instance with keys from [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) objects.
     */
    public function getFromDelegatedPublicKey($publicKey) 
    {
        $apiUrl = $this->getPath('get/forwarded/from-public-key', ["publicKey" => $publicKey]);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createAccountModel($object);
    }

    /**
     * Gets the [AccountMetaData](https://bob.nem.ninja/docs/#accountMetaData) from an account.
     *
     * @param   string  $address    Base32 representation of the account address (T-, N-, M- prefixed addresses).
     * @return  object              Instance with keys from [AccountMetaData](https://bob.nem.ninja/docs/#accountMetaData) objects.
     */
    public function status($address)
    {
        $apiUrl = $this->getPath('status', ["address" => $address]);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createBaseModel($object);
    }

    /**
     * A transaction is said to be incoming with respect to an account if the account is the recipient of the
     * transaction. In the same way outgoing transaction are the transactions where the account is the sender of the
     * transaction. Unconfirmed transactions are those transactions that have not yet been included in a block.
     * Unconfirmed transactions are not guaranteed to be included in any block.
     *
     * @param   string  $address    Base32 representation of the account address (T-, N-, M- prefixed addresses).
     * @param   string  $hash       (Optional) The 256 bit sha3 hash of the transaction up to which transactions are returned.
     * @param   integer $id         (Optional) The transaction id up to which transactions are returned. This parameter will prevail over the hash parameter.
     * @return array                Array of object with keys from [TransactionMetaDataPair](https://bob.nem.ninja/docs/#transactionMetaDataPair) objects.
     */
    public function incomingTransactions($address, $hash = null, $id = null) 
    {
        $params = ["address" => $address];

        if ($hash !== null)
            $params["hash"] = $hash;

        if ($id !== null)
            $params["id"] = $id;

        $apiUrl = $this->getPath('transfers/incoming', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createTransactionCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets an array of transaction meta data pairs where the recipient has the address given as parameter to the
     * request. A maximum of 25 transaction meta data pairs is returned. For details about sorting and discussion of
     * the second parameter see Incoming transactions.
     *
     * @param   string  $address    Base32 representation of the account address (T-, N-, M- prefixed addresses).
     * @param   string  $hash       (Optional) The 256 bit sha3 hash of the transaction up to which transactions are returned.
     * @param   integer $id         (Optional) The transaction id up to which transactions are returned. This parameter will prevail over the hash parameter.
     * @return array                Array of object with keys from [TransactionMetaDataPair](https://bob.nem.ninja/docs/#transactionMetaDataPair) objects.
     */
    public function outgoingTransactions($address, $hash = null, $id = null) 
    {
        $params = ["address" => $address];

        if ($hash !== null)
            $params["hash"] = $hash;

        if ($id !== null)
            $params["id"] = $id;

        $apiUrl = $this->getPath('transfers/outgoing', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createTransactionCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets an array of transaction meta data pairs for which an account is the sender or receiver.
     * A maximum of 25 transaction meta data pairs is returned.
     * For details about sorting and discussion of the second parameter see Incoming transactions.
     *
     * @param   string  $address    Base32 representation of the account address (T-, N-, M- prefixed addresses).
     * @param   string  $hash       (Optional) The 256 bit sha3 hash of the transaction up to which transactions are returned.
     * @param   integer $id         (Optional) The transaction id up to which transactions are returned. This parameter will prevail over the hash parameter.
     * @return array                Array of object with keys from [TransactionMetaDataPair](https://bob.nem.ninja/docs/#transactionMetaDataPair) objects.
     */
    public function allTransactions($address, $hash = null, $id = null)
    {
        $params = ["address" => $address];

        if ($hash !== null)
            $params["hash"] = $hash;

        if ($id !== null)
            $params["id"] = $id;

        $apiUrl = $this->getPath('transfers/all', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createTransactionCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets the array of transactions for which an account is the sender or receiver and which have not yet been
     * included in a block
     *
     * @param   string  $address    Base32 representation of the account address (T-, N-, M- prefixed addresses).
     * @param   string  $hash       (Optional) The 256 bit sha3 hash of the transaction up to which transactions are returned.
     * @return array                Array of object with keys from [TransactionMetaDataPair](https://bob.nem.ninja/docs/#transactionMetaDataPair) objects.
     */
    public function unconfirmedTransactions($address, $hash = null)
    {
        $params = ["address" => $address];

        if ($hash !== null)
            $params["hash"] = $hash;

        $apiUrl = $this->getPath('unconfirmedTransactions', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $data = json_decode($response, true);
        return $this->createTransactionCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets an array of harvest info objects for an account.
     *
     * @param   string  $address    The address of the account.
     * @param   string  %hash       The 256 bit sha3 hash of the block up to which harvested blocks are returned.
     * @return  object              Instance with keys from [HarvestInfo](https://bob.nem.ninja/docs/#harvestInfo) objects.
     */
    public function getHarvestInfo($address, $hash = null) 
    {
        $params = ["address" => $address];

        if ($hash !== null)
            $params["hash"] = $hash;

        $apiUrl = $this->getPath('harvests', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $data = json_decode($response, true);
        return $this->createBaseModel($object); //XXX brr => error/content validation first
    }

    /**
     * Gets an array of account importance view model objects.
     *
     * @param   string  $address    The address of the account.
     * @return  array               Array of object with keys from [AccountImportanceViewModl](https://bob.nem.ninja/docs/#accountImportanceViewModel) objects.
     */
    public function getAccountImportances($address) 
    {
        $params = ["address" => $address];
        $apiUrl = $this->getPath('importances', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createBaseCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets an array of namespace objects for a given account address.
     * The parent parameter is optional. If supplied, only sub-namespaces of the parent namespace are returned.
     *
     * @param   string          $address    The address of the account.
     * @param   null|string     $parent     The (optional) parent namespace id.
     * @param   null|integer    $id         The (optional) namespace database id up to which namespaces are returned.
     * @param   null|integer    $pageSize   The (optional) number of namespaces to be returned.
     * @return  array                       Array of object with keys from [NamespaceMetaDataPair](https://bob.nem.ninja/docs/#namespaceMetaDataPair) objects.
     */
    public function getOwnedNamespaces($address, $parent = null, $id = null, $pageSize = null)
    {
        $params = ["address" => $address];

        if ($hash !== null)
            $params["hash"] = $hash;

        if ($id !== null)
            $params["id"] = $id;

        if ($pageSize !== null)
            $params["pageSize"] = $pageSize;

        $apiUrl = $this->getPath('namespace/page', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createNamespaceCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets an array of mosaic definition objects for a given account address. The parent parameter is optional.
     * If supplied, only mosaic definitions for the given parent namespace are returned.
     * The id parameter is optional and allows retrieving mosaic definitions in batches of 25 mosaic definitions.
     *
     * @param   string          $address    The address of the account.
     * @param   null|string     $parent     The (optional) parent namespace id.
     * @param   null|integer    $id         The (optional) mosaic definition database id up to which mosaic definitions are returned.
     * @return  array                       Array of object with keys from [MosaicDefinitionMetaDataPair](https://bob.nem.ninja/docs/#mosaicDefinitionMetaDataPair) objects.
     */
    public function getCreatedMosaics($address, $parent = null, $id = null) 
    {
        $params = ["address" => $address];

        if ($parent !== null)
            $params["parent"] = $parent;

        if ($id !== null)
            $params["id"] = $id;

        $apiUrl = $this->getPath('mosaic/definition/page', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createMosaicCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets an array of mosaic objects for a given account address.
     *
     * @param   string          $address    The address of the account.
     * @return  array                       Array of object with keys from [MosaicDefinitionMetaDataPair](https://bob.nem.ninja/docs/#mosaicDefinitionMetaDataPair) objects.
     */
    public function getOwnedMosaics($address) 
    {
        $params = ["address" => $address];

        $apiUrl = $this->getPath('mosaic/owned', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createMosaicCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Gets historical information for an account.
     *
     * @param   string          $address        The address of the account.
     * @param   integer         $startHeight    The block height from which on the data should be supplied.
     * @param   integer         $endHeight      The block height up to which the data should be supplied. The end height must be greater than or equal to the start height.
     * @param   integer         $increment      The value by which the height is incremented between each data point. The value must be
     *                                          greater than 0. NIS can supply up to 1000 data points with one request. Requesting more
     *                                          than 1000 data points results in an error.
     * @return  array                           Array of object with keys from [Mosaic](https://bob.nem.ninja/docs/#mosaics) objects.
     */
    public function getHistoricalAccountData($address, $startHeight = null, $endHeight = null, $increment = null)
    {
        $params = ["address" => $address];

        if ($startHeight !== null)
            $params["startHeight"] = $startHeight;

        if ($endHeight !== null)
            $params["endHeight"] = $endHeight;

        if ($increment !== null)
            $params["increment"] = $increment;

        $apiUrl = $this->getPath('historical/get', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response);
        return $this->createBaseCollection($object['data']); //XXX brr => error/content validation first
    }

    /**
     * Each node can allow users to harvest with their delegated key on that node.
     * The NIS configuration has entries for configuring the maximum number of allowed harvesters and optionally allow
     * harvesting only for certain account addresses. The unlock info gives information about the maximum number of
     * allowed harvesters and how many harvesters are already using the node.
     *
     * @return  object      Object with num-unlocked and max-unlocked keys.
     */
    public function getUnlockInfo() 
    {
        $params = [];
        $apiUrl = $this->getPath('unlocked/info', []);
        $response = $this->api->post($apiUrl, $params);

        //XXX include Error checks
        $object = json_decode($response);
        return $this->createBaseModel($object); //XXX brr => error/content validation first
    }

    /**
     * Unlocks an account (starts harvesting).
     *
     * @param host       - string
     * @param privateKey - string
     *
     * @return Array<boolean>
     */
    public function startHarvesting($privateKey) 
    {
        $params = ["value" => $privateKey];

        $apiUrl = $this->getPath('unlock', $params);
        $response = $this->api->post($apiUrl, []);

        //XXX include Error checks
        $object = json_decode($response);
        return $this->createBaseModel($object); //XXX brr => error/content validation first
    }

    /**
     * Locks an account (stops harvesting).
     *
     * @param host       - string
     * @param privateKey - string
     *
     * @return Array<boolean>
     */
    public function stopHarvesting($privateKey) 
    {
        $params = ["value" => $privateKey];

        $apiUrl = $this->getPath('lock', $params);
        $response = $this->api->post($apiUrl, []);

        //XXX include Error checks
        $object = json_decode($response);
        return $this->createBaseModel($object); //XXX brr => error/content validation first
    }
}