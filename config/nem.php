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
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
return [
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
            "use_ssl" => false,
            "host" => "127.0.0.1",
            "port" => 7890,
            "endpoint" => "/",
        ],
        'testing' => [
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
            "endpoint" => "/",
        ],
    ],
];
