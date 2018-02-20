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
namespace NEM\Tests\SDK\NIS;

use NEM\Models\Message;

class DTOMessageTest
    extends NISComplianceTestCase
{
    /**
     * Test *NIS Compliance* of class \NEM\Models\Message.
     *
     * Test basic DTO creation containing messages.
     *
     * @see https://nemproject.github.io/#transaction-data-with-decoded-messages
     * @return void
     */
    public function testNISNullMessageDTOStructure()
    {
        // null message should give empty payload
        $message = new Message(["plain" => null]);
        $message2 = new Message(["plain" => ""]);
        $messageNIS = $message->toDTO();
        $message2NIS = $message2->toDTO();

        // test Message
        $this->assertArrayHasKey("payload", $messageNIS);
        $this->assertArrayHasKey("type", $messageNIS);
        $this->assertArrayHasKey("payload", $message2NIS);
        $this->assertArrayHasKey("type", $message2NIS);
        $this->assertEmpty($message->toHex());
        $this->assertEmpty($message2->toHex());
        $this->assertEquals(Message::TYPE_SIMPLE, $message->type);
        $this->assertEquals(Message::TYPE_SIMPLE, $message2->type);
    }

    /**
     * Test *NIS Compliance* of class \NEM\Models\Message.
     *
     * Test basic DTO creation containing messages.
     *
     * @see https://nemproject.github.io/#transaction-data-with-decoded-messages
     * @return void
     */
    public function testNISMessageToHexFormat()
    {
        // test hexadeximal conversion
        $message = new Message(["plain" => "test"]);
        $messageNIS = $message->toDTO();

        // test Message
        $this->assertArrayHasKey("payload", $messageNIS);
        $this->assertArrayHasKey("type", $messageNIS);
        $this->assertNotEmpty($message->toHex());
        $this->assertEquals(Message::TYPE_SIMPLE, $message->type);
        $this->assertEquals("74657374", $message->toHex());
        $this->assertEquals("74657374", $messageNIS["payload"]);
    }

    /**
     * Data provider for the testNISMessageVectors unit test.
     * 
     * @return array
     */
    public function messageUtf8VectorsProvider()
    {
        return [
            // test UTF-8 characters
            ["Grégory",        "4772c3a9676f7279"],
            ["éöäüèç^<>",      "c3a9c3b6c3a4c3bcc3a8c3a75e3c3e"],
            ["ぁあぃいぼも",     "e38181e38182e38183e38184e381bce38282"],

            // test generic texts
            ["test",           bin2hex("test")],
            ["",               bin2hex("")],
            ["Grégory",        bin2hex("Grégory")],
            ["1234567890",     bin2hex("1234567890")],
            ["€",              bin2hex("€")],
        ];
    }

    /**
     * Unit test for *NIS compliant UTF-8 Message formatting*.
     * 
     * This test makes sure that our hexadecimal representation of
     * messages is compliant to the core PHP implementation.
     * 
     * @dataProvider messageUtf8VectorsProvider
     * @return void
     */
    public function testNISMessageUtf8Vectors($utf8Text, $expectHex)
    {
        $message = new Message(["plain" => $utf8Text]);
        $messageNIS = $message->toDTO();

        $this->assertEquals($expectHex, $message->toHex());
        $this->assertEquals($expectHex, $messageNIS["payload"]);
    }

    /**
     * Data provider for the testNISMessageVectors unit test.
     * 
     * @return array
     */
    public function messageHexVectorsProvider()
    {
        return [
            [bin2hex("test"),     "fe".bin2hex("test")],
            ["d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
             "fe"."d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04"],
        ];
    }

    /**
     * Unit test for *NIS compliant Hex Message formatting*.
     * 
     * This test makes sure that `fe` is prefixed to hexadecimal
     * data in case it must be published as is on the blockchain.
     * 
     * This `fe` prefix is used for example by Apostille.
     * 
     * @dataProvider messageHexVectorsProvider
     * @return void
     */
    public function testNISMessageHexVectors($hexText, $expectHex)
    {
        $message = new Message([
            "type" => Message::TYPE_HEX,
            "plain" => $hexText]);
        $messageNIS = $message->toDTO();

        // test NIS-compliant type override
        $this->assertEquals(Message::TYPE_SIMPLE, $messageNIS["type"]);

        // test hexadecimal message's `fe` prefix
        $this->assertEquals($expectHex, $message->toHex(true));
        $this->assertEquals($expectHex, $messageNIS["payload"]);
    }
}
