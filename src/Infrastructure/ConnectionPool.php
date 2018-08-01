<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2018, Grégory Saive
 */
namespace NEM\Infrastructure;

use NEM\API;

class ConnectionPool
{
    /**
     * List of currently used NEM Nodes.
     * 
     * @var array
     */
    protected $nodes = [
        "mainnet" => [
            "http://hugealice.nem.ninja:7890",
            "http://alice2.nem.ninja:7890",
            "http://alice3.nem.ninja:7890",
            "http://alice4.nem.ninja:7890",
            "http://alice5.nem.ninja:7890",
            "http://alice6.nem.ninja:7890",
            "http://alice7.nem.ninja:7890",
            "http://alice8.nem.ninja:7890",
            "http://alice9.nem.ninja:7890",
            "http://bigalice3.nem.ninja:7890",
        ],
        "testnet" => [
            "http://bigalice2.nem.ninja:7890",
            "http://50.3.87.123:7890",
        ],
        "mijin"   => [],
    ];

    /**
     * List of currently connected Endpoints.
     * 
     * @var array
     */
    protected $endpoints = [
        "mainnet" => [],
        "testnet" => [],
        "mijin"   => [],
    ];

    /**
     * The current connection pool Index.
     * 
     * @var integer
     */
    protected $poolIndex = 0;

    /**
     * The current connection pool network
     * 
     * @var string
     */
    protected $network = "testnet";

    /**
     * Constructor for the NEM ConnectionPool instances.
     * 
     * @param   null|string|integer     $network
     */
    public function __construct($network = "mainnet")
    {
        if (!empty($network) && is_integer($network)) {
            $netId = $network;
            $network = Network::getFromId($netId, "name");
        }
        elseif (!in_array(strtolower($network), ["mainnet", "testnet", "mijin"])) {
            $network = "mainnet";
        }

        $this->network = $network;
        $this->nodes[$network] = array_map(function($item) {
            return preg_replace("/(https?:\/\/)([^:]+)(.*)/", "$2", $item);
        }, $this->nodes[$network]);
    }

    /**
     * Get a connected API using the NEM node configured
     * at the current `poolIndex`
     * 
     * @param   boolean     $forceNew
     * @return  \NEM\API
     */
    public function getEndpoint($forceNew = false)
    {
        $index = $forceNew === true ? ++$this->poolIndex : $this->poolIndex;
        if ($index == count($this->nodes)) {
            $index = 0;
        }

        if (!isset($this->endpoints[$this->network][$index])) {
            $api = new API([
                "use_ssl"  => false,
                "protocol" => "http",
                "host" => $this->nodes[$this->network][$index],
                "port" => 7890,
                "endpoint" => "/",
            ]);

            $this->endpoints[$this->network][$index] = $api;
        }

        $this->poolIndex = $index;
        return $this->endpoints[$this->network][$index];
    }
}
