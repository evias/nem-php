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
    | This option specifies which NIS Node Host must be used for NIS API calls.
    |
    | Default: [
    |              'host' => "127.0.01",
    |              'port' => 7890,
    |              'endpoint' => "/",
    |          ]
    |
    */
    'nis' => [
        "host" => "127.0.0.1",
        "port" => 7890,
        "endpoint" => "/",
    ],
    /*
    |--------------------------------------------------------------------------
    | Default NCC Configuration
    |--------------------------------------------------------------------------
    |
    | This option specifies which NIS Node Host must be used for NIS API calls.
    |
    | Default: [
    |              'host' => "127.0.01",
    |              'port' => 8989,
    |              'endpoint' => "/ncc/api",
    |          ]
    |
    */
    'ncc' => [
        "host" => "127.0.0.1",
        "port" => 8989,
        "endpoint" => "/ncc/api",
    ],
];