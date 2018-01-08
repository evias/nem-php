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

use NEM\Models\Address;
use NEM\Infrastructure\Network;

class AddressDTOTest
    extends NISComplianceTestCase
{
    /**
     * Test *NIS Compliance* of class \NEM\Models\Address.
     *
     * Test basic DTO creation containing addresses.
     *
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     * @return void
     */
    public function testNISAddressDTOStructure()
    {
        // empty account should still always produce NIS compliant
        // objects when converted to Data Transfer Object.
        $address = new Address([]);
        $accountNIS = $address->toDTO();
        $addressOnly = $address->toDTO("address");

        // test AccountMetaDataPair DTO
        $this->assertArrayHasKey("address", $accountNIS);
        $this->assertTrue(is_string($addressOnly));
        $this->assertEmpty($addressOnly);

        // test filled structure
        $testAddress = "TC72N5Y5WFA7KMI2VG3B7T67EQXGB4ATDXCTJGKE";
        $testPublic  = "bc5b32dfe973b89d4f5c246042c9021a1b8bf5d402f747114ed436eb9c914e6a";
        $testPrivate = "don't think I would even put a testnet Private Key ;)";

        $keyPair = new Address([
            "address" => $testAddress,
            "publicKey" => $testPublic,
            "privateKey" => $testPrivate,
        ]);

        $keyPairDTO = $keyPair->toDTO();

        $this->assertArrayHasKey("address", $keyPairDTO);
        $this->assertArrayHasKey("publicKey", $keyPairDTO);
        $this->assertArrayHasKey("privateKey", $keyPairDTO);

        $this->assertEquals($testAddress, $keyPair->address);
        $this->assertEquals($testAddress, $keyPairDTO["address"]);

        $this->assertEquals($testPublic, $keyPair->publicKey);
        $this->assertEquals($testPublic, $keyPairDTO["publicKey"]);

        $this->assertEquals($testPrivate, $keyPair->privateKey);
        $this->assertEquals($testPrivate, $keyPairDTO["privateKey"]);
    }

    /**
     * Test *NIS Compliance* of class \NEM\Models\Address.
     *
     * Test addresses formatting and content creation using
     * the \NEM\Models\Address class.
     *
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     *
     * @depends testNISAddressDTOStructure
     * @return void
     */
    public function testNISAddressDTOFormatting()
    {
        // empty account should still always produce NIS compliant
        // objects when converted to Data Transfer Object.
        $address = new Address(["address"]);
        $accountNIS = $address->toDTO();
        $addressOnly = $address->toDTO("address");

        // test AccountMetaDataPair DTO
        $this->assertArrayHasKey("address", $accountNIS);
        $this->assertTrue(is_string($addressOnly));
        $this->assertEmpty($addressOnly);

        $testClean  = "TC72N5Y5WFA7KMI2VG3B7T67EQXGB4ATDXCTJGKE";
        $testPretty = "TC72N5-Y5WFA7-KMI2VG-3B7T67-EQXGB4-ATDXCT-JGKE";

        $clean  = new Address(["address" => $testClean]);
        $pretty = new Address(["address" => $testPretty]);

        // Address' `address` attribute should always return clean
        // version of the input address.
        $this->assertEquals($testClean, $clean->address);
        $this->assertEquals($testClean, $pretty->address);

        // test toClean() and toPretty() formatting
        $this->assertEquals($clean->toClean(), $pretty->toClean());
        $this->assertEquals($pretty->toPretty(), $clean->toPretty());
    }

    /**
     * Test error *Invalid Public Key Format*.
     *
     * @expectedException \NEM\Errors\NISInvalidPublicKeyFormat
     * @return void
     */
    public function testFromPublicKeyFormatError()
    {
        // load by integer is not possible, public key must be 32 bytes
        // string or 64/66 characters when represented in hexadecimal.
        $addr = Address::fromPublicKey(-43);
    }

    /**
     * Test error *Invalid Network Name*.
     *
     * @expectedException \NEM\Errors\NISInvalidNetworkName
     * @return void
     */
    public function testFromPublicKeyNetworkNameError()
    {
        // invalid network name
        $addr = Address::fromPublicKey("c5f54ba980fcbb657dbaaa42700539b207873e134d2375efeab5f1ab52f87844", "invalid");
    }

    /**
     * Test error *Invalid Network Id*.
     *
     * @expectedException \NEM\Errors\NISInvalidNetworkId
     * @return void
     */
    public function testFromPublicKeyNetworkIdError()
    {
        // invalid network id
        $addr = Address::fromPublicKey("c5f54ba980fcbb657dbaaa42700539b207873e134d2375efeab5f1ab52f87844", 290888);
    }

    /**
     * Test for *generating Address from Public Key* feature.
     *
     * @depends testNISAddressDTOFormatting
     * @dataProvider publicKeyVectorsProvider
     *
     * @param   mixed   $publicKey
     * @param   string  $expectedMainnet
     * @param   string  $expectedTestnet
     * @param   string  $expectedMijin
     * @return  void
     */
    public function testNISAddressDTOLoadFromPublicKey($publicKey, $expectedMainnet, $expectedTestnet, $expectedMijin)
    {
        $mainnet = Address::fromPublicKey($publicKey, Network::$networkInfos["mainnet"]["id"]);
        $testnet = Address::fromPublicKey($publicKey, Network::$networkInfos["testnet"]["id"]);
        $mijin   = Address::fromPublicKey($publicKey, Network::$networkInfos["mijin"]["id"]);

        $this->assertEquals($expectedMainnet, $mainnet->toClean());
        $this->assertEquals(Address::BYTES, strlen($mainnet->toClean()));

        $this->assertEquals($expectedTestnet, $testnet->toClean());
        $this->assertEquals(Address::BYTES, strlen($testnet->toClean()));

        $this->assertEquals($expectedMijin, $mijin->toClean());
        $this->assertEquals(Address::BYTES, strlen($mijin->toClean()));
    }

    /**
     * Data provider for `testNISAddressDTOLoadFromPublicKey` Unit Test.
     *
     * Each row should contain 4 fields in following *strict* order:
     *
     * - publicKey:             String|Buffer|KeyPair containing a hexadecimal representation of the public key.
     * - expectedMainnet:       Base32 string representation of the address on Mainnet.
     * - expectedTestnet:       Base32 string representation of the address on Testnet.
     * - expectedMijin:         Base32 string representation of the address on Mijin.
     *
     * @return array
     */
    public function publicKeyVectorsProvider()
    {
        // @link https://github.com/NemProject/nem-test-vectors
        return [
            ["c5f54ba980fcbb657dbaaa42700539b207873e134d2375efeab5f1ab52f87844", "NDD2CT6LQLIYQ56KIXI3ENTM6EK3D44P5JFXJ4R4", "TDD2CT6LQLIYQ56KIXI3ENTM6EK3D44P5KZPFMK2", "MDD2CT6LQLIYQ56KIXI3ENTM6EK3D44P5LDT7JHT"],
            ["96eb2a145211b1b7ab5f0d4b14f8abc8d695c7aee31a3cfc2d4881313c68eea3", "NABHFGE5ORQD3LE4O6B7JUFN47ECOFBFASC3SCAC", "TABHFGE5ORQD3LE4O6B7JUFN47ECOFBFATE53N2I", "MABHFGE5ORQD3LE4O6B7JUFN47ECOFBFAQ4XDSJH"],
            ["2d8425e4ca2d8926346c7a7ca39826acd881a8639e81bd68820409c6e30d142a", "NAVOZX4HDVOAR4W6K4WJHWPD3MOFU27DFHC7KZOZ", "TAVOZX4HDVOAR4W6K4WJHWPD3MOFU27DFEJDR2PR", "MAVOZX4HDVOAR4W6K4WJHWPD3MOFU27DFEVDXFMY"],
            ["4feed486777ed38e44c489c7c4e93a830e4c4a907fa19a174e630ef0f6ed0409", "NBZ6JK5YOCU6UPSSZ5D3G27UHAPHTY5HDQMGE6TT", "TBZ6JK5YOCU6UPSSZ5D3G27UHAPHTY5HDQCDS5YA", "MBZ6JK5YOCU6UPSSZ5D3G27UHAPHTY5HDSWBYUNP"],
            ["83ee32e4e145024d29bca54f71fa335a98b3e68283f1a3099c4d4ae113b53e54", "NCQW2P5DNZ5BBXQVGS367DQ4AHC3RXOEVGRCLY6V", "TCQW2P5DNZ5BBXQVGS367DQ4AHC3RXOEVFZOQCJ6", "MCQW2P5DNZ5BBXQVGS367DQ4AHC3RXOEVHTXSIR6"],
            ["6d34c04f3a0e42f0c3c6f50e475ae018cfa2f56df58c481ad4300424a6270cbb", "NA5IG3XFXZHIPJ5QLKX2FBJPEZYPMBPPK2ZRC3EH", "TA5IG3XFXZHIPJ5QLKX2FBJPEZYPMBPPKYOTH5YB", "MA5IG3XFXZHIPJ5QLKX2FBJPEZYPMBPPK3MBLSDS"],
            ["a8fefd72a3b833dc7c7ed7d57ed86906dac22f88f1f4331873eb2da3152a3e77", "NAABHVFJDBM74XMJJ52R7QN2MTTG2ZUXPQS62QZ7", "TAABHVFJDBM74XMJJ52R7QN2MTTG2ZUXPQ3F2EPH", "MAABHVFJDBM74XMJJ52R7QN2MTTG2ZUXPQKNEBTU"],
            ["c92f761e6d83d20068fd46fe4bd5b97f4c6ba05d23180679b718d1f3e4fb066e", "NCLK3OLMHR3F2E3KSBUIZ4K5PNWUDN37MLSJBJZP", "TCLK3OLMHR3F2E3KSBUIZ4K5PNWUDN37MIBR5TMD", "MCLK3OLMHR3F2E3KSBUIZ4K5PNWUDN37MJAZV65T"],
            ["eaf16a4833e59370a04ccd5c63395058de34877b48c17174c71db5ed37b537ed", "ND3AHW4VTI5R5QE5V44KIGPRU5FBJ5AFUCJXOY5H", "TD3AHW4VTI5R5QE5V44KIGPRU5FBJ5AFUCOCH2F6", "MD3AHW4VTI5R5QE5V44KIGPRU5FBJ5AFUCBB7RSQ"],
            ["3e2d76fa166407826ce74424b280b21aab3d2e316c88009e21a3542b5c013404", "NCNRCEKHCFEF3O6H2AOROGJ3T2J7W2JWXN64O34P", "TCNRCEKHCFEF3O6H2AOROGJ3T2J7W2JWXMS4I43Q", "MCNRCEKHCFEF3O6H2AOROGJ3T2J7W2JWXPO3P74A"],
            ["9ce3d87e716ae7659f187b6fdb7ad1bbffdae7c281d06f14b6c06ed0276aeace", "NAGFAAZC7J5QW5C2YKVVYTMBCKKB5JADG4HAOCJK", "TAGFAAZC7J5QW5C2YKVVYTMBCKKB5JADG4D2JFGK", "MAGFAAZC7J5QW5C2YKVVYTMBCKKB5JADG5IJESB5"],
            ["2997d62cdf5f78272fa024b755cff7ab847003f977ff8e141289f6527002f9ef", "NAFRD4Q5I5MKCFCZKZXMQTR2KJXM6W73FV3HB7F6", "TAFRD4Q5I5MKCFCZKZXMQTR2KJXM6W73FVP5WXX7", "MAFRD4Q5I5MKCFCZKZXMQTR2KJXM6W73FU6HUKKD"],
            ["a82c794838a1c3d0ea3bb15756d5db93b8f5ee00f35358f0bc5f8d44a1dc64cc", "NDASK5X4M4GT7ILHVTUCJM2JUONV4LI6YPL7MW5C", "TDASK5X4M4GT7ILHVTUCJM2JUONV4LI6YOB2IJFJ", "MDASK5X4M4GT7ILHVTUCJM2JUONV4LI6YOJNXEP4"],
            ["fecff328123d72a7f8ea94908322f9a398912bec353be6956859dbadfe395f66", "NB3HSIHSVZVJPNIBABOTALEBMCAURIFSC5KRHVY2", "TB3HSIHSVZVJPNIBABOTALEBMCAURIFSC6ZSZD46", "MB3HSIHSVZVJPNIBABOTALEBMCAURIFSC4K6YKR7"],
            ["b1c90a93d3c4f93dfebb17317ea6d55eba32746a89be4f98fcf56a7d0e81a914", "ND33EQIYDFJHD2HKLE77ZK7YVXURDCOP74ZZAOMT", "TD33EQIYDFJHD2HKLE77ZK7YVXURDCOP74XNW66N", "MD33EQIYDFJHD2HKLE77ZK7YVXURDCOP75GCWVBD"],
            ["83bcff5475e1ec1db140a69ffc4636e72619135471a8722433f179e9d47b34a6", "NBHDIL3X6VRXAPAZG5DTPGIAI5NB64ULMSLXE7D7", "TBHDIL3X6VRXAPAZG5DTPGIAI5NB64ULMT5VLPPA", "MBHDIL3X6VRXAPAZG5DTPGIAI5NB64ULMRQU2J4R"],
            ["543313d1d27a7a586b5658bb6f7b3067cddb2c9db18bf41e274c38c489a9abf3", "NBZ27MKYWLZIT43WX5N3U3E2DDNPD65MW3YTPBN7", "TBZ27MKYWLZIT43WX5N3U3E2DDNPD65MWZO5Z7GD", "MBZ27MKYWLZIT43WX5N3U3E2DDNPD65MWZOAOT7B"],
            ["5dff2beaafb63a2c158d9ed9b8fb21bc3452f8eac0b38d662c6bef6bb290f810", "NBEPJOCFCT53I4VVERMCR3XR2EOXCW2AGSGMLVCN", "TBEPJOCFCT53I4VVERMCR3XR2EOXCW2AGRUBIDHM", "MBEPJOCFCT53I4VVERMCR3XR2EOXCW2AGRR3SJVI"],
            ["eb78e9736a9ede40ea4eb39725116489d378f34620a98f262d27f070fc098339", "ND56JXJSUJZIJHKANCQ4S7WX7ZOIWMHODXYMLK6C", "TD56JXJSUJZIJHKANCQ4S7WX7ZOIWMHODU2ISAMZ", "MD56JXJSUJZIJHKANCQ4S7WX7ZOIWMHODWL3FFPL"],
            ["230bf2a95734614e2661de4c26429f69215b2f7c9b29f4cedfd5184ad4f5d489", "NADKWZT5CF7TWLPVWJZTOMSTZ6UTQDUO4XTUQV4X", "TADKWZT5CF7TWLPVWJZTOMSTZ6UTQDUO4UPPLCDO", "MADKWZT5CF7TWLPVWJZTOMSTZ6UTQDUO4UF4E6CQ"],
            ["41264de628bd1e569cf672a442adbf880fa56057b523d57916666528eca51907", "NDHPXQXQR65BUN3JYWYBDZ4I4AJNDQEP5MVXVT56", "TDHPXQXQR65BUN3JYWYBDZ4I4AJNDQEP5P6G7THE", "MDHPXQXQR65BUN3JYWYBDZ4I4AJNDQEP5OJDJ3TV"],
            ["ed4817ae396f3328f2e545af54a0b3ab38d047599483b1649734a7d448df86b8", "NB3NFOPJVJ5TGNWJNI72XWMIVQOYJ7ULQN5YDOHK", "TB3NFOPJVJ5TGNWJNI72XWMIVQOYJ7ULQPMLUU74", "MB3NFOPJVJ5TGNWJNI72XWMIVQOYJ7ULQPZLOEJ2"],
            ["160860a65c86a49c7cdda83b97c9e0436106739b50b07d70ddd78b3bce476862", "NCL5K4MJ6JZRARX6CPDPNCLRJLQVQDC7L5URS4ZU", "TCL5K4MJ6JZRARX6CPDPNCLRJLQVQDC7L4J5S2PN", "MCL5K4MJ6JZRARX6CPDPNCLRJLQVQDC7L63JWDL4"],
            ["870306246ffa22463a4b6a987423c2b6a5c8ff10e752ef69b144a6ef790b1017", "NC3M6BTSFQMZ2A4IQTYE6D2AJ3MLNI6JK6P4TTTZ", "TC3M6BTSFQMZ2A4IQTYE6D2AJ3MLNI6JK66BZHEJ", "MC3M6BTSFQMZ2A4IQTYE6D2AJ3MLNI6JK6HU26L2"],
            ["986a9b32779bab5d0057fbc2e675530b7da1529ab07015c919c05124ad169501", "NCJ7LDS5SRKRKOTEFCD56THA46FANWT7WY3EOZQF", "TCJ7LDS5SRKRKOTEFCD56THA46FANWT7W2N6KR65", "MCJ7LDS5SRKRKOTEFCD56THA46FANWT7WYVYPIP7"],
            ["73df95e3859f05666c65e19e013bb925836eef88e10530cb91c001daac8f6693", "NAM4FLOJDTFBXTBRCZDGUCUJYKASAJZI65SE35JL", "TAM4FLOJDTFBXTBRCZDGUCUJYKASAJZI64OXGKAO", "MAM4FLOJDTFBXTBRCZDGUCUJYKASAJZI65NASNZ2"],
            ["4d7414ec83b76d0ad5dc79c562dcccf4f22f2ebd92718a2b2b0d1d3e3c85ba23", "NBKQQNB5SFDVMJZHHP7YLSN24KB7VUO2BVVCHBI7", "TBKQQNB5SFDVMJZHHP7YLSN24KB7VUO2BV7XBTCS", "MBKQQNB5SFDVMJZHHP7YLSN24KB7VUO2BWVHQUAK"],
            ["94c84681732ad1b631954f62bf489c8095fa3e0e7084aab2517f7408a3f01b06", "NAMUZ4IQ5ZRQRTWRKLNN5CPQL3BVSG5IUYU7ZTVW", "TAMUZ4IQ5ZRQRTWRKLNN5CPQL3BVSG5IUZ7R7W5J", "MAMUZ4IQ5ZRQRTWRKLNN5CPQL3BVSG5IUZ54I6X4"],
            ["3f2416d2bf6fd1af6ee046a26c7e3d1805bb284b5f3a637d80d10e497219498d", "NBS6TBC7EWVSTAAWOJHXBR6ZIJZSBS7DJ7CGOE3M", "TBS6TBC7EWVSTAAWOJHXBR6ZIJZSBS7DJ7JUQTOL", "MBS6TBC7EWVSTAAWOJHXBR6ZIJZSBS7DJ76352GA"],
            ["042cf87afdebfc76f8832189551ec55c8cd6d8e96e05d7c7a4dcd960270176a5", "ND2NHSREVEIENBOBZ2CHQ4R53JIICOV3LD65R6OK", "TD2NHSREVEIENBOBZ2CHQ4R53JIICOV3LDTU3WC3", "MD2NHSREVEIENBOBZ2CHQ4R53JIICOV3LB2LGKCB"],
            ["220ec954d8dbe44251bbd7cc7a959338bec3e9352d047d32311be0c2156e4c77", "NB427ZDP4AG4O27FWD6TUANGNP6Z6OKS7OK4X6BP", "TB427ZDP4AG4O27FWD6TUANGNP6Z6OKS7NXQV4BL", "MB427ZDP4AG4O27FWD6TUANGNP6Z6OKS7OB457F2"],
            ["d12b79c041ad4fed9e6e48033a6db355da550729672304ff7b8237b155d2afbc", "NCEY6OUMB4O4J5CCNO3HYUI5H3L4Q7SZSEA7OY2A", "TCEY6OUMB4O4J5CCNO3HYUI5H3L4Q7SZSFYUIQSQ", "MCEY6OUMB4O4J5CCNO3HYUI5H3L4Q7SZSHY22W3Z"],
            ["3217c925877ed52244c8179f64eaf7a3cf56ac5380809f88622e92abf0d2d872", "NAKZMGJ566JPIMKSADPQETFOTT2EHF4JAGJW6D2K", "TAKZMGJ566JPIMKSADPQETFOTT2EHF4JAFKP7KZX", "MAKZMGJ566JPIMKSADPQETFOTT2EHF4JAEILDTYQ"],
            ["f31858b48cb385a269f77947be4550fa5140896c2b92c900e96cf5a77e0096b3", "NBVWU2JBVVEJG4GZNCLEZ5VPYJYIYZQZR2KHMBVX", "TBVWU2JBVVEJG4GZNCLEZ5VPYJYIYZQZRY25VZ64", "MBVWU2JBVVEJG4GZNCLEZ5VPYJYIYZQZR3I3ODIT"],
            ["03dde9b92105db1d12af0b85ba0420172243d3cd058902af45ca3459e425c6b5", "NBJ2GH6ICYY2TI3BD7F7AMKIZGRNQ7W2EMI6TQ2E", "TBJ2GH6ICYY2TI3BD7F7AMKIZGRNQ7W2ENLCFIFT", "MBJ2GH6ICYY2TI3BD7F7AMKIZGRNQ7W2EP2AYRD5"],
            ["10a3d8df40b9c14f1d8b4a304851cafafe2eca4407e1ae646b193cc34ed82a8f", "NCLFXH2ZZ2PROR6C5W653PMHQ3FVOSA4GSIWNAOP", "TCLFXH2ZZ2PROR6C5W653PMHQ3FVOSA4GQFIF2NW", "MCLFXH2ZZ2PROR6C5W653PMHQ3FVOSA4GTIFSRGQ"],
            ["3b6e4c2111a0930d4c3670b26907e4ffe401d2f9ec19f5d835d0f227e725f693", "NAJWDIEGODZXG7SKBCR43R6YDWZBTOLYD2UJCTOB", "TAJWDIEGODZXG7SKBCR43R6YDWZBTOLYDYGGAISP", "MAJWDIEGODZXG7SKBCR43R6YDWZBTOLYDY7WS2LZ"],
            ["d6c77fac24e1a168eeebfade47a5f92c64f3116958fc5d37e9d2651efdce8afa", "ND4EBPWVLUB26GCUUTJ5V7T3WY2Z4FBCV6V7HK4J", "TD4EBPWVLUB26GCUUTJ5V7T3WY2Z4FBCV5CKDQJM", "MD4EBPWVLUB26GCUUTJ5V7T3WY2Z4FBCV6FEJO6E"],
            ["3d48d0d8256019d8c50d150e9def7700c175d8904243add628808176073c185c", "NAIUXXVGTVRDA5EOWKECEBTXRPLT7UBTFH6UK2GP", "TAIUXXVGTVRDA5EOWKECEBTXRPLT7UBTFEIB2KA5", "MAIUXXVGTVRDA5EOWKECEBTXRPLT7UBTFFK6NUE5"],
            ["15f0b1b8d79842e31b0b0088cb55c0aef361b504b826e6ac25e355ce8da32cc0", "NCPZBSLBZIWESLCAMLQM5M5GACQRHAMAR32FWCXF", "TCPZBSLBZIWESLCAMLQM5M5GACQRHAMARZVQ4ZJG", "MCPZBSLBZIWESLCAMLQM5M5GACQRHAMAR24RDA6B"],
            ["9ccd9a5981d53b993c9fcb8b5832588a177894c6cacaa39bbc805472fb71edbb", "NDV3IAHTYCV2LP4D6DDJXOZK4FWONV66N55YDOH7", "TDV3IAHTYCV2LP4D6DDJXOZK4FWONV66N6JL34TP", "MDV3IAHTYCV2LP4D6DDJXOZK4FWONV66N75FVCGV"],
            ["150a0befb7f1fd480e1f9d992193dc8f552af9e6ff787e9cd67ab48a0ca75022", "NCL6KDXGZRF4PNPCX6TKIMTAKECA4PX7IBKM7Z5V", "TCL6KDXGZRF4PNPCX6TKIMTAKECA4PX7IBZCMQ4Z", "MCL6KDXGZRF4PNPCX6TKIMTAKECA4PX7IDZZXIJ7"],
            ["d5ae508dc0506b2b5aef58f120f6612fbc0a641c86eacf1b618debac55dd0f15", "NBSA2DORBTOBMGNNTKP6K3Q5IMFWAOPBVJLLBNAE", "TBSA2DORBTOBMGNNTKP6K3Q5IMFWAOPBVIUOREL6", "MBSA2DORBTOBMGNNTKP6K3Q5IMFWAOPBVJ3QVMFP"],
            ["95a3e84725b97af7fb03c5d6f2d7523d4a919fd321cb7e7e4509c99801e2b7d4", "NCFTSEFVMWPNY3C2METRDX2H7G6KBYD6MK6NXPDK", "TCFTSEFVMWPNY3C2METRDX2H7G6KBYD6MIYKDKFQ", "MCFTSEFVMWPNY3C2METRDX2H7G6KBYD6MIOGGBEC"],
            ["40ac854730d9eb4a099d9b64d30fd2a353639ef3180b9df602a0f4e571be66b7", "NCADB46DN43PEKXPGJVSZBEWE4MFXZOJM5N4HCIP", "TCADB46DN43PEKXPGJVSZBEWE4MFXZOJM7J2I7RK", "MCADB46DN43PEKXPGJVSZBEWE4MFXZOJM4M2KHVD"],
            ["2f618766dcc0ee11351e6f6149dfac930158efb6e4ed2d8c6b535ba5128bfe6e", "NAWNZ3CSGABVKONYVOA4IEBJG3VMIMVI5DVCKA43", "TAWNZ3CSGABVKONYVOA4IEBJG3VMIMVI5BHK323C", "MAWNZ3CSGABVKONYVOA4IEBJG3VMIMVI5AQZ42SR"],
            ["9ab0e3301d77cce80f84f0abdc4990d9d8b6108094fe25d7984e60dbd7188aca", "NDQENBUQ5L3FO6KAUZIWJVHXW2LWV7TIXAQOJ73X", "TDQENBUQ5L3FO6KAUZIWJVHXW2LWV7TIXCOTIZQQ", "MDQENBUQ5L3FO6KAUZIWJVHXW2LWV7TIXC22MEWE"],
            ["d6ad0f3b1f4a9bbc6477de87a772a563e8bf286335b305a7449cddd1d1d8cd3d", "ND5DM7OICDZCQCI4JU45UALV33SGTCOXXZL6DDPG", "TD5DM7OICDZCQCI4JU45UALV33SGTCOXXY4YZV4F", "MD5DM7OICDZCQCI4JU45UALV33SGTCOXX26TSBZS"],
            ["625988c63c760ba61eee40b369f36192a78bdceb4ca729f3c4c9bead238700f7", "NCWCKPBD5KIL2SP45FQYCBJPXKU2EEJXQSLGXGZ7", "TCWCKPBD5KIL2SP45FQYCBJPXKU2EEJXQS6ZGPQD", "MCWCKPBD5KIL2SP45FQYCBJPXKU2EEJXQQSZVTXT"],
            ["6d5e263a1186b0f1dc32de29171cd6c34092b1aa6c7f18c0220917d278282de2", "NB3KV6MO67QBT7VYBMVOWVHSCCUNGXPADKRVQQKC", "TB3KV6MO67QBT7VYBMVOWVHSCCUNGXPADIGPIYTG", "MB3KV6MO67QBT7VYBMVOWVHSCCUNGXPADILKO32U"],
        ];
    }
}
