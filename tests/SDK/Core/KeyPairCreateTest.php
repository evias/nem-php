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
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Tests\SDK\Core;

use NEM\Tests\TestCase;
use NEM\Core\KeyPair;
use NEM\Contracts\KeyPair as KeyPairContract;
use NEM\Core\Buffer;

class KeyPairCreateTest
    extends TestCase
{
    /**
     * Unit test for *KeyPair Cloning*.
     *
     * @return void
     */
    public function testCreateValidKeyPair()
    {
        $kp1 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");
        $kp2 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");

        // should always create the same KeyPair content !
        $this->assertEquals($kp1->getPrivateKey(), $kp2->getPrivateKey());
        $this->assertEquals($kp1->getSecretKey(), $kp2->getSecretKey());
        $this->assertEquals($kp1->getPublicKey(), $kp2->getPublicKey());

        $publicShouldBe = "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04";
        $this->assertEquals($publicShouldBe, $kp1->getPublicKey());
    }

    /**
     * Unit test for *Random KeyPair creation*.
     *
     * This should produce a randomly generated KeyPair.
     *
     * @depends testCreateValidKeyPair
     * @return void
     */
    public function testCreateRandomKeyPair()
    {
        $kp = KeyPair::create();

        // check contract and class..
        $this->assertInstanceOf(KeyPair::class, $kp);
        $this->assertInstanceOf(KeyPairContract::class, $kp);

        // check KeyPair content
        $this->assertEquals(64, strlen($kp->getPrivateKey()));
        $this->assertEquals(64, strlen($kp->getSecretKey()));
        $this->assertEquals(64, strlen($kp->getPublicKey()));
        $this->assertTrue(ctype_xdigit($kp->getPrivateKey()));
        $this->assertTrue(ctype_xdigit($kp->getSecretKey()));
        $this->assertTrue(ctype_xdigit($kp->getPublicKey()));

        // validate SECRET KEY creation. The secret key contains
        // the *reversed hexadecimal representation* of the private key.
        $buf = Buffer::fromHex($kp->getPrivateKey());
        $flipped = $buf->flip();

        $this->assertEquals($flipped->getHex(), $kp->getSecretKey());

        // should *deterministically* create keys.
        $priv = $kp->getPrivateKey();
        $newKp = KeyPair::create($priv); // create from private key hex

        $this->assertEquals($kp->getPrivateKey(), $newKp->getPrivateKey());
        $this->assertEquals($kp->getSecretKey(), $newKp->getSecretKey());
        $this->assertEquals($kp->getPublicKey(), $newKp->getPublicKey());
    }

    /**
     * Unit test for *KeyPair Cloning*.
     *
     * @depends testCreateRandomKeyPair
     * @return void
     */
    public function testKeyPairCloning()
    {
        $kp = KeyPair::create();
        $clone = KeyPair::create($kp);

        // validate internal KeyPair content cloning
        $this->assertEquals($kp->getPrivateKey(), $clone->getPrivateKey());
        $this->assertEquals($kp->getSecretKey(), $clone->getSecretKey());
        $this->assertEquals($kp->getPublicKey(), $clone->getPublicKey());
    }

    /**
     * Unit test for *Private Key Buffer Cloning*.
     *
     * @depends testCreateRandomKeyPair
     * @return void
     */
    public function testPrivateKeyBufferCloning()
    {
        $kp = KeyPair::create("0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef");
        $privateBuffer = Buffer::fromHex("0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef", 32);
        $clone = KeyPair::create($privateBuffer);

        // validate internal buffer content cloning
        $this->assertEquals($privateBuffer->getHex(), $clone->getPrivateKey());
        $this->assertEquals($kp->getPrivateKey(), $clone->getPrivateKey());
        $this->assertEquals($kp->getSecretKey(), $clone->getSecretKey());
        $this->assertEquals($kp->getPublicKey(), $clone->getPublicKey());
    }

    /**
     * Data provider for the testKeyPairVectors unit test.
     *
     * @return array
     */
    public function keypairVectorsProvider()
    {
        return [
            ["e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5", "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04"],
            // NCC 66 bytes private keys
            ["00f8cd8e07478559a64b7d2be6b92a55d9b63ec0f0bd3d4b7686ee6f524113c490", "a9092549008e7f4965ce140004e736378e91d2f7e67e5fb8729a14b1bf764780"],
        ];
    }

    /**
     * Test content initialization for KeyPair class.
     *
     * @depends testCreateValidKeyPair
     * @dataProvider keypairVectorsProvider
     *
     * @param   string  $privateKey
     * @param   string  $expectedPublicKey
     * @return void
     */
    public function testKeyPairVectors($privateKey, $expectedPublicKey)
    {
        $kp = KeyPair::create($privateKey);
        $this->assertEquals($expectedPublicKey, $kp->getPublicKey());
    }
}
