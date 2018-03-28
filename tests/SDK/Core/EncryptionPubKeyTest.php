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
namespace NEM\Tests\SDK\Core;

use NEM\Tests\TestCase;
use NEM\Core\KeyPair;
use NEM\Core\KeyGenerator;
use NEM\Core\Buffer;
use NEM\Core\Encryption;
use NEM\Core\EncryptedPayload;

class EncryptionPubKeyTest
    extends TestCase
{
    public function testKeyDerivation()
    {
        // prepare
        $salt = Buffer::fromHex("9d21c9343c8b20afc7fe4735c3a15d178816896a169b64bcda926e46b19dc2fd");
        $sender = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");
        $recipient = KeyPair::create("a55041066883e108d3c06a644a0b541656b9b6cc530b558ae618bda89b3edefa");

        // act
        $generator = new KeyGenerator;
        $derivedKey = $generator->deriveKey($salt, $sender->getSecretKey(), $recipient->getPublicKey());

        // expected values/assert
        $expectedHex = "a92faca4d0e3fee28f5e56508f52e1decccd1a0d5477fd906afa9aea50a62a02";
        $expectedSize = 32;
        $this->assertEquals($expectedSize, $derivedKey->getSize());
        $this->assertEquals($expectedHex, $derivedKey->getHex());
    }

    /**
     * Unit test for base encryption mechanism.
     * 
     * @return void
     */
    public function testBaseEncryption()
    {
        $sender = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");
        $recipient = KeyPair::create("a55041066883e108d3c06a644a0b541656b9b6cc530b558ae618bda89b3edefa");

        $encryptedPayload = Encryption::encrypt($sender, $recipient, "Hello, World!", "hex");
        $expectedHex = "e8b2c6fcd0e915bc028a8f5ea229323610625f662c96a21a8991de50a0948f48a6078075824107476dc7178201d29e96cd54379d96638e73e25f209284b560e2";
        $expectedKey = $recipient->getPublicKey()->getBinary();

        $this->assertInstanceOf(\NEM\Core\EncryptedPayload::class, $encryptedPayload);
        $this->assertEquals($expectedKey, $encryptedPayload->getKey()->getBinary());
    }
}
