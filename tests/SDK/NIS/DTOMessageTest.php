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
        $messageNIS = $message->toDTO();

        // test Message
        $this->assertArrayHasKey("payload", $messageNIS);
        $this->assertArrayHasKey("type", $messageNIS);
        $this->assertEmpty($message->toHex());
        $this->assertEquals(Message::TYPE_SIMPLE, $message->type);
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
            ["test",           bin2hex("test")],
            ["",               bin2hex("")],
            ["Grégory",        bin2hex("Grégory")],
            ["1234567890",     bin2hex("1234567890")],
        ];
    }

    /**
     * Unit test for *NIS compliant UTF-8 Message formatting*.
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
        ];
    }

    /**
     * Unit test for *NIS compliant UTF-8 Message formatting*.
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
