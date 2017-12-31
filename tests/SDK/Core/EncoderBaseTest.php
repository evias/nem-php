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
namespace NEM\Tests\SDK\Buffer;

use NEM\Core\Buffer;
use NEM\Core\Encoder;
use NEM\Core\KeyPair;
use NEM\Tests\TestCase;

class EncodreBaseTest
    extends TestCase
{
    /**
     * Data provider for the *testBufferUInt8Encoding* unit test.
     *
     * @return array
     */
    public function uint8EncodingVectorsProvider()
    {
        return [
            ["e5fc25dde93bc3554417e491886ead34a6989c80684e9c203dfadb1e33847ce7", implode(", ", [229, 252, 37, 221, 233, 59, 195, 85, 68, 23, 228, 145, 136, 110, 173, 52, 166, 152, 156, 128, 104, 78, 156, 32, 61, 250, 219, 30, 51, 132, 124, 231])],
            ["96eb2a145211b1b7ab5f0d4b14f8abc8d695c7aee31a3cfc2d4881313c68eea3", implode(", ", [150, 235, 42, 20, 82, 17, 177, 183, 171, 95, 13, 75, 20, 248, 171, 200, 214, 149, 199, 174, 227, 26, 60, 252, 45, 72, 129, 49, 60, 104, 238, 163])],
            ["2d8425e4ca2d8926346c7a7ca39826acd881a8639e81bd68820409c6e30d142a", implode(", ", [45, 132, 37, 228, 202, 45, 137, 38, 52, 108, 122, 124, 163, 152, 38, 172, 216, 129, 168, 99, 158, 129, 189, 104, 130, 4, 9, 198, 227, 13, 20, 42])],
            ["4feed486777ed38e44c489c7c4e93a830e4c4a907fa19a174e630ef0f6ed0409", implode(", ", [79, 238, 212, 134, 119, 126, 211, 142, 68, 196, 137, 199, 196, 233, 58, 131, 14, 76, 74, 144, 127, 161, 154, 23, 78, 99, 14, 240, 246, 237, 4, 9])],
            ["83ee32e4e145024d29bca54f71fa335a98b3e68283f1a3099c4d4ae113b53e54", implode(", ", [131, 238, 50, 228, 225, 69, 2, 77, 41, 188, 165, 79, 113, 250, 51, 90, 152, 179, 230, 130, 131, 241, 163, 9, 156, 77, 74, 225, 19, 181, 62, 84])],
            ["6d34c04f3a0e42f0c3c6f50e475ae018cfa2f56df58c481ad4300424a6270cbb", implode(", ", [109, 52, 192, 79, 58, 14, 66, 240, 195, 198, 245, 14, 71, 90, 224, 24, 207, 162, 245, 109, 245, 140, 72, 26, 212, 48, 4, 36, 166, 39, 12, 187])],
            ["a8fefd72a3b833dc7c7ed7d57ed86906dac22f88f1f4331873eb2da3152a3e77", implode(", ", [168, 254, 253, 114, 163, 184, 51, 220, 124, 126, 215, 213, 126, 216, 105, 6, 218, 194, 47, 136, 241, 244, 51, 24, 115, 235, 45, 163, 21, 42, 62, 119])],
            ["c92f761e6d83d20068fd46fe4bd5b97f4c6ba05d23180679b718d1f3e4fb066e", implode(", ", [201, 47, 118, 30, 109, 131, 210, 0, 104, 253, 70, 254, 75, 213, 185, 127, 76, 107, 160, 93, 35, 24, 6, 121, 183, 24, 209, 243, 228, 251, 6, 110])],
            ["eaf16a4833e59370a04ccd5c63395058de34877b48c17174c71db5ed37b537ed", implode(", ", [234, 241, 106, 72, 51, 229, 147, 112, 160, 76, 205, 92, 99, 57, 80, 88, 222, 52, 135, 123, 72, 193, 113, 116, 199, 29, 181, 237, 55, 181, 55, 237])],
            ["3e2d76fa166407826ce74424b280b21aab3d2e316c88009e21a3542b5c013404", implode(", ", [62, 45, 118, 250, 22, 100, 7, 130, 108, 231, 68, 36, 178, 128, 178, 26, 171, 61, 46, 49, 108, 136, 0, 158, 33, 163, 84, 43, 92, 1, 52, 4])],
        ];
    }

    /**
     * Unit test for *encoding in unsigned integer on 8-bits*.
     *
     * This unit test makes sure that conversion from hexadecimal representation
     * to a unsigned integer on 8-bits (unsigned char) works fine.
     *
     * UInt8 arrays are used internally to swap bytes more easily and prepend
     * additional paddings required by NIS (version byte, network id).
     *
     * @dataProvider uint8EncodingVectorsProvider
     * 
     * @param   string  $hex
     * @param   string  $expectedUInt8Array
     * @return  void
     */
    public function testBufferUInt8Encoding($hex, $expectedUInt8Array)
    {
        $this->assertEquals($expectedUInt8Array, implode(", ", Buffer::fromHex($hex)->toUInt8()));
    }

    /**
     * Data provider for the *testUInt8ToBinary* unit test.
     *
     * @return array
     */
    public function uint8ToBinaryVectorsProvider()
    {
        return [
            ["9ce3d87e716ae7659f187b6fdb7ad1bbffdae7c281d06f14b6c06ed0276aeace", hex2bin("9ce3d87e716ae7659f187b6fdb7ad1bbffdae7c281d06f14b6c06ed0276aeace")],
            ["2997d62cdf5f78272fa024b755cff7ab847003f977ff8e141289f6527002f9ef", hex2bin("2997d62cdf5f78272fa024b755cff7ab847003f977ff8e141289f6527002f9ef")],
            ["a82c794838a1c3d0ea3bb15756d5db93b8f5ee00f35358f0bc5f8d44a1dc64cc", hex2bin("a82c794838a1c3d0ea3bb15756d5db93b8f5ee00f35358f0bc5f8d44a1dc64cc")],
            ["fecff328123d72a7f8ea94908322f9a398912bec353be6956859dbadfe395f66", hex2bin("fecff328123d72a7f8ea94908322f9a398912bec353be6956859dbadfe395f66")],
            ["b1c90a93d3c4f93dfebb17317ea6d55eba32746a89be4f98fcf56a7d0e81a914", hex2bin("b1c90a93d3c4f93dfebb17317ea6d55eba32746a89be4f98fcf56a7d0e81a914")],
            ["83bcff5475e1ec1db140a69ffc4636e72619135471a8722433f179e9d47b34a6", hex2bin("83bcff5475e1ec1db140a69ffc4636e72619135471a8722433f179e9d47b34a6")],
            ["543313d1d27a7a586b5658bb6f7b3067cddb2c9db18bf41e274c38c489a9abf3", hex2bin("543313d1d27a7a586b5658bb6f7b3067cddb2c9db18bf41e274c38c489a9abf3")],
            ["5dff2beaafb63a2c158d9ed9b8fb21bc3452f8eac0b38d662c6bef6bb290f810", hex2bin("5dff2beaafb63a2c158d9ed9b8fb21bc3452f8eac0b38d662c6bef6bb290f810")],
            ["eb78e9736a9ede40ea4eb39725116489d378f34620a98f262d27f070fc098339", hex2bin("eb78e9736a9ede40ea4eb39725116489d378f34620a98f262d27f070fc098339")],
            ["230bf2a95734614e2661de4c26429f69215b2f7c9b29f4cedfd5184ad4f5d489", hex2bin("230bf2a95734614e2661de4c26429f69215b2f7c9b29f4cedfd5184ad4f5d489")],
        ];
    }

    /**
     * Unit test for *encode UInt8 to Binary string*.
     *
     * This unit test makes sure that conversion from UInt8 arrays
     * to a binary strings works fine.
     *
     * @depends testBufferUInt8Encoding
     * @dataProvider uint8ToBinaryVectorsProvider
     * @return void
     */
    public function testUInt8ToBinary($hex, $expectedBinary)
    {
        $enc = new Encoder();
        $buf = Buffer::fromHex($hex);
        $this->assertEquals($expectedBinary, $enc->ua2bin($buf->toUInt8()));
    }

    /**
     * Data provider for the *testUInt8ToInt32* unit test.
     *
     * @return array
     */
    public function uint8ToInt32VectorsProvider()
    {
        return [
            ["41264de628bd1e569cf672a442adbf880fa56057b523d57916666528eca51907", implode(", ", [1093029350, 683482710, -1661570396, 1118683016, 262496343, -1255942791, 375809320, -324724473])],
            ["ed4817ae396f3328f2e545af54a0b3ab38d047599483b1649734a7d448df86b8", implode(", ", [-314042450, 963588904, -219855441, 1419817899, 953173849, -1803308700, -1758156844, 1222608568])],
            ["160860a65c86a49c7cdda83b97c9e0436106739b50b07d70ddd78b3bce476862", implode(", ", [369647782, 1552327836, 2094901307, -1748377533, 1627812763, 1353743728, -573076677, -834181022])],
            ["870306246ffa22463a4b6a987423c2b6a5c8ff10e752ef69b144a6ef790b1017", implode(", ", [-2029844956, 1878663750, 978021016, 1948500662, -1513554160, -413995159, -1320900881, 2030768151])],
            ["986a9b32779bab5d0057fbc2e675530b7da1529ab07015c919c05124ad169501", implode(", ", [-1737843918, 2006690653, 5766082, -428518645, 2107724442, -1334831671, 432034084, -1391028991])],
            ["73df95e3859f05666c65e19e013bb925836eef88e10530cb91c001daac8f6693", implode(", ", [1944032739, -2053175962, 1818616222, 20691237, -2089881720, -519753525, -1849687590, -1399888237])],
            ["4d7414ec83b76d0ad5dc79c562dcccf4f22f2ebd92718a2b2b0d1d3e3c85ba23", implode(", ", [1299453164, -2085130998, -706971195, 1658637556, -231788867, -1838052821, 722279742, 1015396899])],
            ["94c84681732ad1b631954f62bf489c8095fa3e0e7084aab2517f7408a3f01b06", implode(", ", [-1798814079, 1932186038, 831868770, -1085760384, -1778762226, 1887742642, 1367307272, -1544545530])],
            ["3f2416d2bf6fd1af6ee046a26c7e3d1805bb284b5f3a637d80d10e497219498d", implode(", ", [1059329746, -1083190865, 1860191906, 1820212504, 96151627, 1597662077, -2133782967, 1914259853])],
            ["042cf87afdebfc76f8832189551ec55c8cd6d8e96e05d7c7a4dcd960270176a5", implode(", ", [70056058, -34866058, -125623927, 1428079964, -1932076823, 1845876679, -1529030304, 654407333])],
        ];
    }

    /**
     * Unit test for *encode UInt8 to Int32 (WordArray)*.
     * 
     * This unit test makes sure that UInt8 arrays can be converted
     * to Int32 (WordArray) representation.
     *
     * @depends testBufferUInt8Encoding
     * @dataProvider uint8ToInt32VectorsProvider
     * 
     * @param   string  $hex
     * @param   string  $expectedWordArray
     * @return  void
     */
    public function testUInt8ToInt32($hex, $expectedWordArray)
    {
        $enc = new Encoder();
        $buf = Buffer::fromHex($hex);
        $this->assertEquals($expectedWordArray, implode(", ", $enc->ua2words($buf->toUInt8())));
    }

    /**
     * Data provider for the *testUInt8ToInt32* unit test.
     *
     * @return array
     */
    public function uint8ToHexadecimalVectorsProvider()
    {
        return [
            ["220ec954d8dbe44251bbd7cc7a959338bec3e9352d047d32311be0c2156e4c77", "220ec954d8dbe44251bbd7cc7a959338bec3e9352d047d32311be0c2156e4c77"],
            ["d12b79c041ad4fed9e6e48033a6db355da550729672304ff7b8237b155d2afbc", "d12b79c041ad4fed9e6e48033a6db355da550729672304ff7b8237b155d2afbc"],
            ["3217c925877ed52244c8179f64eaf7a3cf56ac5380809f88622e92abf0d2d872", "3217c925877ed52244c8179f64eaf7a3cf56ac5380809f88622e92abf0d2d872"],
            ["f31858b48cb385a269f77947be4550fa5140896c2b92c900e96cf5a77e0096b3", "f31858b48cb385a269f77947be4550fa5140896c2b92c900e96cf5a77e0096b3"],
            ["03dde9b92105db1d12af0b85ba0420172243d3cd058902af45ca3459e425c6b5", "03dde9b92105db1d12af0b85ba0420172243d3cd058902af45ca3459e425c6b5"],
            ["10a3d8df40b9c14f1d8b4a304851cafafe2eca4407e1ae646b193cc34ed82a8f", "10a3d8df40b9c14f1d8b4a304851cafafe2eca4407e1ae646b193cc34ed82a8f"],
            ["3b6e4c2111a0930d4c3670b26907e4ffe401d2f9ec19f5d835d0f227e725f693", "3b6e4c2111a0930d4c3670b26907e4ffe401d2f9ec19f5d835d0f227e725f693"],
            ["d6c77fac24e1a168eeebfade47a5f92c64f3116958fc5d37e9d2651efdce8afa", "d6c77fac24e1a168eeebfade47a5f92c64f3116958fc5d37e9d2651efdce8afa"],
            ["3d48d0d8256019d8c50d150e9def7700c175d8904243add628808176073c185c", "3d48d0d8256019d8c50d150e9def7700c175d8904243add628808176073c185c"],
            ["15f0b1b8d79842e31b0b0088cb55c0aef361b504b826e6ac25e355ce8da32cc0", "15f0b1b8d79842e31b0b0088cb55c0aef361b504b826e6ac25e355ce8da32cc0"],
        ];
    }

    /**
     * Unit test for *encode UInt8 to Hexadecimal*.
     * 
     * This unit test makes sure that UInt8 arrays can be converted
     * back to hexadecimal representation without losing data.
     *
     * @depends testBufferUInt8Encoding
     * @dataProvider uint8ToHexadecimalVectorsProvider
     * 
     * @param   string  $hex
     * @param   string  $expectedHex
     * @return void
     */
    public function testUInt8ToHexadecimal($hex, $expectedHex)
    {
        $enc = new Encoder();
        $buf = Buffer::fromHex($hex);
        $this->assertEquals($expectedHex, $enc->ua2hex($buf->toUInt8()));
    }
}
