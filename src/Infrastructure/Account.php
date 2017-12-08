<?php

namespace NEM\Infrastructure;

use NEM\NemSDK;

class Account 
{

    public $nemSDK;
    private $endpoint = "/account";

    public function __construct( NemSDK $nemSDK ) {
        $this->nemSDK = $nemSDK;
    }
	
    /**
     * Gets an [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) for an account.
     *
     * @param   string  address     Address
     * @return  array               Array of [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) objects.
     */
    public function getFromAddress($address) 
    {
        $apiUrl = $this->endpoint . '/get?' . http_build_query(["address" => $address]);
        $response = $this->nemSDK->api->getJSON($apiUrl);
        return json_decode($response);
    }

	/**
	 * Gets an AccountInfoWithMetaData for an account with publicKey
	 *
	 * @param publicKey - NEM
	 *
	 * @return Array<AccountInfoWithMetaData>
	 */
	public function getFromPublicKey( $publicKey ) {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'get/from-public-key?publicKey=' . $publicKey, "" ) );
	}

	/**
	 * Given a delegate (formerly known as remote) account's address, gets the AccountMetaDataPair for the account for
	 * which the given account is the delegate account. If the given account address is not a delegate account for any
	 * account, the request returns the AccountMetaDataPair for the given address.
	 *
	 * @param address - Address
	 *
	 * @return Array<AccountInfoWithMetaData>
	 */
	public function getOriginalAccountDataFromDelegatedAccountAddress( $address ) {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'get/forwarded?address=' . $address, "" ) );
	}

	/**
	 * retrieve the original account data by providing the public key of the delegate account.
	 *
	 * @param publicKey - string
	 *
	 * @return Array<AccountInfoWithMetaData>
	 */
	public function getOriginalAccountDataFromDelegatedAccountPublicKey( $publicKey ) {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'get/forwarded/from-public-key?publicKey=' . $publicKey, "" ) );
	}

	/**
	 * Gets the AccountMetaData from an account.
	 *
	 * @param address - NEM Address
	 *
	 * @return Array<AccountStatus>
	 */
	public function status( $address ) {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'status?address=' . $address, "" ) );
	}

	/**
	 * A transaction is said to be incoming with respect to an account if the account is the recipient of the
	 * transaction. In the same way outgoing transaction are the transactions where the account is the sender of the
	 * transaction. Unconfirmed transactions are those transactions that have not yet been included in a block.
	 * Unconfirmed transactions are not guaranteed to be included in any block
	 *
	 * @param address   - The address of the account.
	 * @param hash      - (Optional) The 256 bit sha3 hash of the transaction up to which transactions are returned.
	 * @param id        - (Optional) The transaction id up to which transactions are returned. This parameter will
	 *                  prevail over hash.
	 *
	 * @return Array<Transaction[]>
	 */
	public function incomingTransactions( $address, $hash = null, $id = null ) {
		$query = 'address=' . $address;
		if ( $hash !== null ) {
			$query .= '&hash=' . $hash;
		}
		if ( $id !== null ) {
			$query .= '&id=' . $id;
		}

		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'transfers/incoming?' . $query, "" ) )->data;
	}

	/**
	 * Gets an array of transaction meta data pairs where the recipient has the address given as parameter to the
	 * request. A maximum of 25 transaction meta data pairs is returned. For details about sorting and discussion of
	 * the second parameter see Incoming transactions.
	 *
	 * @param address   - The address of the account.
	 * @param hash      - (Optional) The 256 bit sha3 hash of the transaction up to which transactions are returned.
	 * @param id        - (Optional) The transaction id up to which transactions are returned. This parameter will
	 *                  prevail over hash.
	 *
	 * @return array
	 */
	public function outgoingTransactions( $address, $hash = null, $id = null ) {

		$query = 'address=' . $address;
		if ( $hash !== null ) {
			$query .= '&hash=' . $hash;
		}
		if ( $id !== null ) {
			$query .= '&id=' . $id;
		}

		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'transfers/outgoing?' . $query, "" ) )->data;
	}

	/**
	 * Gets an array of transaction meta data pairs for which an account is the sender or receiver.
	 * A maximum of 25 transaction meta data pairs is returned.
	 * For details about sorting and discussion of the second parameter see Incoming transactions.
	 *
	 * @param address   - The address of the account.
	 * @param hash      - (Optional) The 256 bit sha3 hash of the transaction up to which transactions are returned.
	 * @param id        - (Optional) The transaction id up to which transactions are returned. This parameter will
	 *                  prevail over hash.
	 *
	 * @return Array<Transaction[]>
	 */
	public function allTransactions( $address, $hash = null, $id = null ) {

		$query = 'address=' . $address;
		if ( $hash !== null ) {
			$query .= '&hash=' . $hash;
		}
		if ( $id !== null ) {
			$query .= '&id=' . $id;
		}

		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'transfers/all?' . $query, "" ) )->data;
	}

	public function allTransactionsPageable( $address, $rows = 100 ) {
		$transactions = $this->allTransactions( $address );
		$lastHash     = ( end( $transactions ) )->meta->hash->data;
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

	/**
	 * Gets the array of transactions for which an account is the sender or receiver and which have not yet been
	 * included in a block
	 *
	 * @param address - NEM Address
	 *
	 * @return Array<Transaction[]>
	 */
	public function unconfirmedTransactions( $address ) {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'unconfirmedTransactions?address=' . $address, "" ) );
	}

	/**
	 * Gets an array of harvest info objects for an account.
	 *
	 * @param address - Address
	 * @param hash    - string
	 *
	 * @return Array<AccountHarvestInfo[]>
	 */
	public function getHarvestInfoDataForAnAccount( $address, $hash ) {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'harvests?address=' . $address . '&hash=' . $hash, "" ) );
	}

	/**
	 * Gets an array of account importance view model objects.
	 * @return Array<AccountImportanceInfo[]>
	 */
	public function getAccountImportances( $address ) {
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'importances' . $address, "" ) );
	}

	/**
	 * Gets an array of namespace objects for a given account address.
	 * The parent parameter is optional. If supplied, only sub-namespaces of the parent namespace are returned.
	 *
	 * @param address  - Address
	 * @param parent   - The optional parent namespace id.
	 * @param id       - The optional namespace database id up to which namespaces are returned.
	 * @param pageSize - The (optional) number of namespaces to be returned.
	 *
	 * @return Array<Namespace[]>
	 */
	public function getNamespaceOwnedByAddress( $address, $parent = null, $id = null, $pageSize = null ) {

		$query = 'address=' . $address;
		if ( $parent !== null ) {
			$query .= '&parent=' . $parent;
		}
		if ( $id !== null ) {
			$query .= '&id=' . $id;
		}
		if ( $pageSize !== null ) {
			$query .= '&pageSize=' . $pageSize;
		}

		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'namespace/page?' . $query, "" ) );
	}

	/**
	 * Gets an array of mosaic definition objects for a given account address. The parent parameter is optional.
	 * If supplied, only mosaic definitions for the given parent namespace are returned.
	 * The id parameter is optional and allows retrieving mosaic definitions in batches of 25 mosaic definitions.
	 *
	 * @param address - The address of the account.
	 * @param parent  - The optional parent namespace id.
	 * @param id      - The optional mosaic definition database id up to which mosaic definitions are returned.
	 *
	 * @return Array<MosaicDefinition[]>
	 */
	public function getMosaicCreatedByAddress( $address, $parent = null, $id = null ) {

		$query = 'address=' . $address;
		if ( $parent !== null ) {
			$query .= '&parent=' . $parent;
		}
		if ( $id !== null ) {
			$query .= '&id=' . $id;
		}

		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'mosaic/definition/page?' . $query, "" ) )->data;
	}

	/**
	 * Gets an array of mosaic objects for a given account address.
	 *
	 * @param address - Address
	 *
	 * @return Array<Mosaic[]>
	 */
	public function getMosaicOwnedByAddress( $address ) {
		$address = $this->nemSDK->models()->address($address)->plain();
		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'mosaic/owned?address=' . $address, "" ) )->data;
	}

	/**
	 * Unlocks an account (starts harvesting).
	 *
	 * @param host       - string
	 * @param privateKey - string
	 *
	 * @return Array<boolean>
	 */
	public function unlockHarvesting( $host, $privateKey ) {
		//todo create unlockHarvesting
	}

	/**
	 * Locks an account (stops harvesting).
	 *
	 * @param host       - string
	 * @param privateKey - string
	 *
	 * @return Array<boolean>
	 */
	public function lockHarvesting( $host, $privateKey ) {
		//todo lockHarvesting
	}

	/**
	 * Each node can allow users to harvest with their delegated key on that node.
	 * The NIS configuration has entries for configuring the maximum number of allowed harvesters and optionally allow
	 * harvesting only for certain account addresses. The unlock info gives information about the maximum number of
	 * allowed harvesters and how many harvesters are already using the node.
	 * @return Array<NodeHarvestInfo>
	 */
	public function unlockInfo() {
		//Todo create unlockInfo
	}

	/**
	 * Gets historical information for an account.
	 *
	 * @param address       - The address of the account.
	 * @param startHeight   - The block height from which on the data should be supplied.
	 * @param endHeight     - The block height up to which the data should be supplied. The end height must be greater
	 *                      than or equal to the start height.
	 * @param increment     - The value by which the height is incremented between each data point. The value must be
	 *                      greater than 0. NIS can supply up to 1000 data points with one request. Requesting more
	 *                      than 1000 data points results in an error.
	 *
	 * @return Array    <AccountHistoricalInfo[]>
	 */
	public function getHistoricalAccountData( $address, $startHeight, $endHeight, $increment ) {

		$query = 'address=' . $address;
		if ( $startHeight !== null ) {
			$query .= '&startHeight=' . $startHeight;
		}
		if ( $endHeight !== null ) {
			$query .= '&endHeight=' . $endHeight;
		}
		if ( $increment !== null ) {
			$query .= '&increment=' . $increment;
		}

		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'historical/get?' . $query, "" ) );
	}

}