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
namespace NEM\Core;

use NEM\Core\Buffer;

/**
 * This is the EncryptedPayload class
 *
 * This class provides with a layer of abstraction for
 * Encrypted Payload storage. Payloads are composed of
 * 3 fields being: ciphertext, iv and key.
 */
class EncryptedPayload
{
    /**
     * The payload ciphertext property.
     * 
     * @var string
     */
    private $cipherText = null;

    /**
     * The encryption input vector.
     * 
     * @var string
     */
    private $inputVector = null;

    /**
     * The recipient public key
     * 
     * @var NEM\Core\Buffer
     */
    private $publicKey = null;

    /**
     * Helper to prepare a `data` attribute into a \NEM\Core\Buffer
     * object for easier internal data representations.
     * 
     * @param   null|string|\NEM\Core\Buffer    $data   The data that needs to be added to the returned Buffer.
     * @return  \NEM\Core\Buffer
     */
    protected static function prepareInputBuffer($data)
    {
        if ($data instanceof Buffer) {
            return $data;
        }
        elseif (is_string($data) && ctype_xdigit($data)) {
            return Buffer::fromHex($data);
        }
        elseif (is_string($data)) {
            return new Buffer($data);
        }
        elseif (is_array($data)) {
            // Uint8 provided (serialized data)
            return Buffer::fromUInt8($data);
        }

        return new Buffer((string) $data);
    }

    /**
     * Constructor for the EncryptedPayload class.
     * 
     * This will build an encrypted payload.
     * 
     * @param   string|NEM\Core\Buffer  $cipher
     * @param   string|NEM\Core\Buffer  $iv
     * @param   string|NEM\Core\Buffer  $key
     * @return  void
     */
    public function __construct($cipher, $iv, $key)
    {
        $this->cipherText  = self::prepareInputBuffer($cipher);
        $this->inputVector = self::prepareInputBuffer($iv);
        $this->publicKey   = self::prepareInputBuffer($key);
    }

    /**
     * Helper method to retrieve the plain array data of the
     * encrypted payload instance
     * 
     * @return array
     */
    public function toArray()
    {
        return [
            "ciphertext" => $this->cipherText->getBinary(),
            "iv" => $this->inputVector->getBinary(),
            "key" => $this->publicKey->getBinary()
        ];
    }

    /**
     * Helper method to encode the payload content in an array
     * and return the JSON formatted string.
     * 
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * Helper method to encode the payload content to Base64
     * representation.
     * 
     * This method will first convert the content to a JSON
     * represented format.
     * 
     * @return string
     */
    public function toBase64()
    {
        return base64_encode($this->toJSON());
    }

    /**
     * Getter for the cipherText property
     * 
     * @return NEM\Core\Buffer
     */
    public function getCipher()
    {
        return $this->cipherText;
    }

    /**
     * Getter for the inputVector property
     * 
     * @return NEM\Core\Buffer
     */
    public function getIV()
    {
        return $this->inputVector;
    }

    /**
     * Getter for the publicKey property
     * 
     * @return NEM\Core\Buffer
     */
    public function getKey()
    {
        return $this->publicKey;
    }
}
