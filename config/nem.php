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
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Default HTTP Handler
    |--------------------------------------------------------------------------
    |
    | This option specifies which HTTP Handler Class
    |
    | Following libraries are currently available: "guzzle"
    */
    'handler_class' => \evias\NEMBlockchain\Handlers\GuzzleRequestHandler::class,

    /*
    |--------------------------------------------------------------------------
    | Default Networks Configuration
    |--------------------------------------------------------------------------
    |
    | This option specifies the available NEM networks.
    |
    | Mainnet, Testnet and Mijin are the 3 available NEM networks.
    | @see https://github.com/NemProject/NanoWallet/blob/master/src/app/utils/Network.js
    */
    'networks' => [
        'Mainnet' => [
            'id' => 104,
            'prefix' => "68",
            'char' => "N",
        ],
        'Testnet' => [
            'id' => -104,
            'prefix' => "98",
            'char' => "T",
        ],
        'Mijin' => [
            'id' => 96,
            'prefix' => "60",
            'char' => "M",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default NIS Configuration
    |--------------------------------------------------------------------------
    |
    | This option specifies which NIS Nodes must be used for NIS API calls.
    |
    | The `nis.primary` node configuration corresponds to the preferred node for
    | NIS API calls.
    |
    | Testing environment should always use online nodes to avoid compatibility
    | issues outside your local network [which could be due to a change on your
    | localhost nodes].
    */
    'nis' => [
        'primary' => [
	        "protocol" => "http",
            "use_ssl" => false,
            "host" => "127.0.0.1",
            "port" => 7890,
            "endpoint" => "/",
        ],
        'testing' => [
        	"protocol" => "http",
            "use_ssl" => false,
            "host" => "go.nem.ninja",
            "port" => 7890,
            "endpoint" => "/",
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Default NCC Configuration
    |--------------------------------------------------------------------------
    |
    | This option specifies which NCC Nodes must be used for NCC API calls.
    |
    | The `ncc.primary` node configuration corresponds to the preferred node for
    | NCC API calls.
    |
    | Testing environment should always use online nodes to avoid compatibility
    | issues outside your local network [which could be due to a change on your
    | localhost nodes].
    */
    'ncc' => [
        'primary' => [
            "use_ssl" => false,
            "host" => "127.0.0.1",
            "port" => 8989,
            "endpoint" => "/ncc/api",
        ],
        'testing' => [
            "use_ssl" => false,
            "host" => "go.nem.ninja",
            "port" => 8989,
            "endpoint" => "/ncc/api",
        ],
    ],
];
