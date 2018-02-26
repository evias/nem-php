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
namespace NEM\Models;

use NEM\Core\Encryption as CryptoHelper;
use RuntimeException;

/**
 * This is the Message class
 *
 * This class extends the NEM\Models\Model class
 * to provide with an integration of NEM's message 
 * payload objects.
 * 
 * @link https://nemproject.github.io/#transaction-data-with-decoded-messages
 */
class Message
    extends Model
{
    /**
     * @internal
     * @var integer
     */
    public const TYPE_HEX = 0;

    /**
     * @internal
     * @var integer
     */
    public const TYPE_SIMPLE = 1;

    /**
     * @internal
     * @var integer
     */
    public const TYPE_ENCRYPTED = 2;

    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "plain",
        "payload",
        "type",
    ];

    /**
     * Account DTO represents NIS API's [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair).
     *
     * @see [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair)
     * @return  array       Associative array containing a NIS *compliable* account representation.
     */
    public function toDTO($filterByKey = null) 
    {
        // CryptoHelper will store a KeyPair when necessary
        // to allow encryption (needs private + public keys)
        $helper = new CryptoHelper();
        $plain  = $this->getPlain();

        if (empty($plain) || !isset($this->type))
            $this->type = Message::TYPE_SIMPLE;

        if ($this->type == Message::TYPE_HEX) {
            if (! ctype_xdigit($plain)) {
                throw new RuntimeException("Invalid hexadecimal representation. Use Message::TYPE_SIMPLE instead of Message::TYPE_HEX.");
            }

            // hexadecimal message content
            $payload = "fe" . $plain;
            $this->type = Message::TYPE_SIMPLE;
        }
        elseif ($this->type == Message::TYPE_SIMPLE) {
            // simple message, unencrypted
            if (empty($plain)) {
                $plain = $this->toPlain();
                $payload = $this->payload;
            }
            else {
                $payload = $this->toHex();
            }
        }
        elseif ($this->type == Message::TYPE_ENCRYPTED) {
            // encrypted message
            $payload = $helper->encrypt($plain);

            //XXX HW Trezor include "publicKey" to DTO.
        }

        return [
            "payload" => $payload,
            "type"    => $this->type,
        ];
    }

    /**
     * Helper to retrieve the hexadecimal representation of a message.
     *
     * @return string
     */
    public function toHex($prefixHexContent = false)
    {
        $chars = $this->getPlain();
        if ($prefixHexContent && ctype_xdigit($chars)) {
            return "fe" . $chars;
        }

        $payload = "";
        for ($c = 0, $cnt = strlen($chars); $c < $cnt; $c++ ) {
            $decimal = ord($chars[$c]);
            $hexCode = dechex($decimal);

            // payload is built of *hexits*
            $payload .= str_pad($hexCode, 2, "0", STR_PAD_LEFT);
        }

        $this->payload = strtolower($payload);
        return $this->payload;
    }

    /**
     * Helper to retrieve the UTF8 representation of a message
     * payload (hexadecimal representation).
     *
     * @return string
     */
    public function toPlain($hex = null)
    {
        if (empty($this->payload) && empty($hex))
            return "";

        $plain = "";
        $payload = $hex ?: $this->payload;
        for ($c = 0, $cnt = strlen($payload); $c < $cnt; $c += 2) {
            $hex = substr($payload, $c, 2);
            $decimal = hexdec($hex);
            $plain  .= chr($decimal);
        }

        return ($this->plain = $plain);
    }

    /**
     * Setter for the plaintext content of a NEM Message.
     * 
     * @param   string  $plain
     * @return  \NEM\Models\Message
     */
    public function setPlain($plain)
    {
        $this->plain = $plain;
        return $this;
    }

    /**
     * Getter for the plaintext content of a NEM Message.
     * 
     * @param   string  $plain
     * @return  \NEM\Models\Message
     */
    public function getPlain()
    {
        return $this->plain;
    }
}