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
namespace NEM\Tests\SDK\NIS\Serialize;

use NEM\Tests\TestCase;
use NEM\Core\Serializer;
use NEM\Core\Buffer;
use NEM\Models\Model;
use NEM\Models\Fee;
use NEM\Models\TimeWindow;

use NEM\Models\Transaction\MosaicDefinition;
use NEM\Models\MosaicDefinition as DefinitionDTO;
use NEM\Models\Mosaic;
use NEM\Models\MosaicProperties;
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicLevy;

class SerializeTransactionMosaicDefinitionTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicDefinition_NoLevy()
    {
        // prepare monster mosaic definition data..

        $mosaic = new Mosaic([
            "namespaceId" => "evias",
            "name" => "test-mosaicdef",
        ]);
        $txData = [
            "creationFeeSink" => "TBMOSAICOD4F54EE5CDMR23CCBGOAM2XSJBR5OLC",
            "creationFee" => 10000000,
            // sub-dto NEM\Models\MosaicDefinition
            "mosaicDefinition" => (new DefinitionDTO([
                "id" => $mosaic->toDTO(),
                "creator" => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
                "description" => "this is a mosaic description with é ü ~e Utf-8 characters in it. Also unescaped HTML: <a href=\"github.com/evias\">My Github</a>",
                "properties" => (new MosaicProperties([
                    new MosaicProperty(["name" => "divisibility", "value" => 4]),
                    new MosaicProperty(["name" => "initialSupply", "value" => 290888]),
                    new MosaicProperty(["name" => "supplyMutable", "value" => true]),
                    new MosaicProperty(["name" => "transferable", "value" => true]),
                ]))->toDTO(),
            ]))->toDTO(),
            "timeStamp" => (new TimeWindow(["timeStamp" => 91813487]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91817087]))->toDTO(),
            "fee"       => Fee::NAMESPACE_AND_MOSAIC,
            "version"   => -1744830463,
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature" => "c23866625c24b09f3aa2e4ce164e08fa0cbb40b0dd7cfa9b2d1a2d624ef0a60f6a425ee61aef19fac35cef65d93d1a56e52032ed17813a39f9f55e99bc74c203",
        ];

        $transaction = new MosaicDefinition($txData);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            1,  64,   0,   0,   1,   0,   0, 152,
          111, 246, 120,   5,  32,   0,   0,   0,
          217,  12,   8, 207, 187, 249,  24, 217,
           48,  77, 221,  69, 246,  67,  37, 100,
          195, 144, 165, 250, 207, 243, 223,  23,
          237,  92,   9, 108,  76, 207,  13,   4,
          240,  73,   2,   0,   0,   0,   0,   0,
          127,   4, 121,   5,  64,   1,   0,   0,
           32,   0,   0,   0, 217,  12,   8, 207,
          187, 249,  24, 217,  48,  77, 221,  69,
          246,  67,  37, 100, 195, 144, 165, 250,
          207, 243, 223,  23, 237,  92,   9, 108,
           76, 207,  13,   4,  27,   0,   0,   0,
            5,   0,   0,   0, 101, 118, 105,  97,
          115,  14,   0,   0,   0, 116, 101, 115,
          116,  45, 109, 111, 115,  97, 105,  99,
          100, 101, 102, 128,   0,   0,   0, 116,
          104, 105, 115,  32, 105, 115,  32,  97,
           32, 109, 111, 115,  97, 105,  99,  32,
          100, 101, 115,  99, 114, 105, 112, 116,
          105, 111, 110,  32, 119, 105, 116, 104,
           32, 195, 169,  32, 195, 188,  32, 126,
          101,  32,  85, 116, 102,  45,  56,  32,
           99, 104,  97, 114,  97,  99, 116, 101,
          114, 115,  32, 105, 110,  32, 105, 116,
           46,  32,  65, 108, 115, 111,  32, 117,
          110, 101, 115,  99,  97, 112, 101, 100,
           32,  72,  84,  77,  76,  58,  32,  60,
           97,  32, 104, 114, 101, 102,  61,  34,
          103, 105, 116, 104, 117,  98,  46,  99,
          111, 109,  47, 101, 118, 105,  97, 115,
           34,  62,  77, 121,  32,  71, 105, 116,
          104, 117,  98,  60,  47,  97,  62,   4,
            0,   0,   0,  21,   0,   0,   0,  12,
            0,   0,   0, 100, 105, 118, 105, 115,
          105,  98, 105, 108, 105, 116, 121,   1,
            0,   0,   0,  52,  27,   0,   0,   0,
           13,   0,   0,   0, 105, 110, 105, 116,
          105,  97, 108,  83, 117, 112, 112, 108,
          121,   6,   0,   0,   0,  50,  57,  48,
           56,  56,  56,  25,   0,   0,   0,  13,
            0,   0,   0, 115, 117, 112, 112, 108,
          121,  77, 117, 116,  97,  98, 108, 101,
            4,   0,   0,   0, 116, 114, 117, 101,
           24,   0,   0,   0,  12,   0,   0,   0,
          116, 114,  97, 110, 115, 102, 101, 114,
           97,  98, 108, 101,   4,   0,   0,   0,
          116, 114, 117, 101,   0,   0,   0,   0,
           40,   0,   0,   0,  84,  66,  77,  79,
           83,  65,  73,  67,  79,  68,  52,  70,
           53,  52,  69,  69,  53,  67,  68,  77,
           82,  50,  51,  67,  67,  66,  71,  79,
           65,  77,  50,  88,  83,  74,  66,  82,
           53,  79,  76,  67, 128, 150, 152,   0,
            0,   0,   0,   0
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "01400000010000986ff6780520000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04f0490200000000007f0479054001000020000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d041b0000000500000065766961730e000000746573742d6d6f7361696364656680000000746869732069732061206d6f73616963206465736372697074696f6e207769746820c3a920c3bc207e65205574662d38206368617261637465727320696e2069742e20416c736f20756e657363617065642048544d4c3a203c6120687265663d226769746875622e636f6d2f6576696173223e4d79204769746875623c2f613e04000000150000000c00000064697669736962696c69747901000000341b0000000d000000696e697469616c537570706c7906000000323930383838190000000d000000737570706c794d757461626c650400000074727565180000000c0000007472616e7366657261626c650400000074727565000000002800000054424d4f534149434f443446353445453543444d523233434342474f414d3258534a4252354f4c438096980000000000";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }

    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicDefinition_WithLevy()
    {
        // prepare monster mosaic definition data..

        $mosaic = new Mosaic([
            "namespaceId" => "evias",
            "name" => "test-mosaicdef-levy",
        ]);
        $xem = new Mosaic([
            "namespaceId" => "nem",
            "name" => "xem",
        ]);

        // prepare mosaic definition
        $txData = [
            "creationFeeSink" => "TBMOSAICOD4F54EE5CDMR23CCBGOAM2XSJBR5OLC",
            "creationFee" => 10000000,
            // sub-dto NEM\Models\MosaicDefinition
            "mosaicDefinition" => (new DefinitionDTO([
                "id" => $mosaic->toDTO(),
                "creator" => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
                "description" => "this is a mosaic description with é ü ~e Utf-8 characters in it. Also unescaped HTML: <a href=\"github.com/evias\">My Github</a>",
                "properties" => (new MosaicProperties([
                    new MosaicProperty(["name" => "divisibility", "value" => 0]),
                    new MosaicProperty(["name" => "initialSupply", "value" => 290888]),
                    new MosaicProperty(["name" => "supplyMutable", "value" => true]),
                    new MosaicProperty(["name" => "transferable", "value" => true]),
                ]))->toDTO(),
                "levy"  => (new MosaicLevy([
                    "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                    "fee" => 100,
                    "type" => MosaicLevy::TYPE_ABSOLUTE,
                    "mosaicId" => $xem->toDTO()
                ]))->toDTO(),
            ]))->toDTO(),
            "timeStamp" => (new TimeWindow(["timeStamp" => 91819841]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91823441]))->toDTO(),
            "fee"       => Fee::NAMESPACE_AND_MOSAIC,
            "version"   => -1744830463,
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature" => "df9025da189b2a1bc91816df7bb75f3bbccc85c2e7da92414bf033b019af22a06305cda5d184db23359ebd5de40ff5cd85e9db05543bea643241ec8ef1151706",
        ];

        $transaction = new MosaicDefinition($txData);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [1,64,0,0,1,0,0,152,65,15,121,5,32,0,0,0,217,12,8,207,187,249,24,217,48,77,221,69,246,67,37,100,195,144,165,250,207,243,223,23,237,92,9,108,76,207,13,4,240,73,2,0,0,0,0,0,81,29,121,5,143,1,0,0,32,0,0,0,217,12,8,207,187,249,24,217,48,77,221,69,246,67,37,100,195,144,165,250,207,243,223,23,237,92,9,108,76,207,13,4,32,0,0,0,5,0,0,0,101,118,105,97,115,19,0,0,0,116,101,115,116,45,109,111,115,97,105,99,100,101,102,45,108,101,118,121,128,0,0,0,116,104,105,115,32,105,115,32,97,32,109,111,115,97,105,99,32,100,101,115,99,114,105,112,116,105,111,110,32,119,105,116,104,32,195,169,32,195,188,32,126,101,32,85,116,102,45,56,32,99,104,97,114,97,99,116,101,114,115,32,105,110,32,105,116,46,32,65,108,115,111,32,117,110,101,115,99,97,112,101,100,32,72,84,77,76,58,32,60,97,32,104,114,101,102,61,34,103,105,116,104,117,98,46,99,111,109,47,101,118,105,97,115,34,62,77,121,32,71,105,116,104,117,98,60,47,97,62,4,0,0,0,21,0,0,0,12,0,0,0,100,105,118,105,115,105,98,105,108,105,116,121,1,0,0,0,48,27,0,0,0,13,0,0,0,105,110,105,116,105,97,108,83,117,112,112,108,121,6,0,0,0,50,57,48,56,56,56,25,0,0,0,13,0,0,0,115,117,112,112,108,121,77,117,116,97,98,108,101,4,0,0,0,116,114,117,101,24,0,0,0,12,0,0,0,116,114,97,110,115,102,101,114,97,98,108,101,4,0,0,0,116,114,117,101,74,0,0,0,1,0,0,0,40,0,0,0,84,68,87,90,53,53,82,53,86,73,72,83,72,53,87,87,75,54,67,69,71,65,73,80,55,68,51,53,88,86,70,90,51,82,85,50,83,53,85,81,14,0,0,0,3,0,0,0,110,101,109,3,0,0,0,120,101,109,100,0,0,0,0,0,0,0,40,0,0,0,84,66,77,79,83,65,73,67,79,68,52,70,53,52,69,69,53,67,68,77,82,50,51,67,67,66,71,79,65,77,50,88,83,74,66,82,53,79,76,67,128,150,152,0,0,0,0,0];
        $expectSize = count($expectUInt8);
        $expectHex  = "0140000001000098410f790520000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04f049020000000000511d79058f01000020000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d042000000005000000657669617313000000746573742d6d6f736169636465662d6c65767980000000746869732069732061206d6f73616963206465736372697074696f6e207769746820c3a920c3bc207e65205574662d38206368617261637465727320696e2069742e20416c736f20756e657363617065642048544d4c3a203c6120687265663d226769746875622e636f6d2f6576696173223e4d79204769746875623c2f613e04000000150000000c00000064697669736962696c69747901000000301b0000000d000000696e697469616c537570706c7906000000323930383838190000000d000000737570706c794d757461626c650400000074727565180000000c0000007472616e7366657261626c6504000000747275654a00000001000000280000005444575a3535523556494853483557574b36434547414950374433355856465a33525532533555510e000000030000006e656d0300000078656d64000000000000002800000054424d4f534149434f443446353445453543444d523233434342474f414d3258534a4252354f4c438096980000000000";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}
