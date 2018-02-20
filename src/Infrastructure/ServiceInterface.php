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
 * @version    0.0.2
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Infrastructure;

interface ServiceInterface
{
    /**
     * Setter for the `endpoint` property.
     *
     * @param   string  $endpoint
     * @return  \NEM\Infrastructure\Abstract
     */
    public function setBaseUrl($endpoint);

    /**
     * Getter for the `endpoint` property.
     *
     * @return string
     */
    public function getBaseUrl();
}
