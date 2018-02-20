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
 * @license    MIT License
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Traits;

use NEM\Core\Serializer;

/**
 * This is the Serializable trait
 *
 * This trait simply provides with an instance of the
 * \NEM\Core\Serializer for any object that implements
 * it.
 */
trait Serializable
{
    /**
     * Instance of the NEM serializer
     *
     * @var \NEM\Core\Serializer
     */
    private $serializer;

    /**
     * Getter for the `serializer` property.
     * 
     * @return \NEM\Core\Serializer
     */
    public function getSerializer()
    {
        if (! $this->serializer || ! ($this->serializer instanceof Serializer)) {
            $this->serializer = Serializer::getInstance();
        }

        return $this->serializer;
    }

    /**
     * Getter for the `serializer` property.
     * 
     * @param  \NEM\Core\Serializer
     * @return \NEM\Traits\Serializable
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }
}
