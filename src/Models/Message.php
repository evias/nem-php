<?php<?php
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
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM\Models;

class Message
    extends Model
{
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
        "payload",
        "type",
    ];

    /**
     * Store the plain text representation of the message
     *
     * @var string
     */
    protected $plain;

    /**
     * Account DTO represents NIS API's [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair).
     *
     * @see [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair)
     * @return  array       Associative array containing a NIS *compliable* account representation.
     */
    public function toDTO() 
    {
        if ()


        return [
            "payload" => $this->toHex(),
            "type"    => $this->type,
        ];
    }

    /**
     * Helper to retrieve the hexadecimal representation of a message.
     *
     * @return string
     */
    public function toHex()
    {
        $chars = $this->plain;
        $payload = "";
        for ($c = 0, $cnt = strlen($chars); $c < $cnt; $c++ ) {
            $decimal = ord($chars[$c]);
            $hexCode = dechex($decimal);

            // payload is built of *hexits*
            $payload .= strpad($hexCode, 2, "0", STR_PAD_LEFT);
        }

        $this->payload = strtoupper($payload);
        return $this->payload;
    }

    public function toPlain($hex = null)
    {
        if (empty($this->payload) && empty($hex))
            return "";

        $plain = "";
        $payload = $hex ?: $this->payload;
        for ($c = 0, $cnt = strlen($payload); $c < $cnt; $c += 2) {
            $hex = substr($hex, $c, 2);
            $decimal = hexdec($hex);
            $plain  .= chr($decimal);
        }

        return $plain;
    }

    /**
     * TRYS to evaluate if the message is hex
     *
     * @param $string
     *
     * @return bool
     */
    private function isHex( $string ) {
        if ( ctype_xdigit( $string ) ) {
            return true;
        }

        return false;
    }

}