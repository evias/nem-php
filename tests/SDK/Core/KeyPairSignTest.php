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
use NEM\Contracts\KeyPair as KeyPairContract;
use NEM\Core\Buffer;

use kornrunner\Keccak;
use \desktopd\SHA3\Sponge as Keccak_SHA3;
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Ed25519 as Ed25519;
use \ParagonIE_Sodium_Core_X25519 as Ed25519ref10;

class KeyPairSignTest
    extends TestCase
{
    /**
     * Unit test for *Basic KeyPair Signing*.
     * 
     * This test makes sure that KeyPair signatures are created
     * with Keccak-512 when no different algorithm is specified.
     *
     * @link https://github.com/trezor/trezor-crypto/blob/master/test_check.c#L3256
     * @return void
     */
    public function testBasicKeyPairSignIsKeccak512()
    {
        // @link https://github.com/trezor/trezor-crypto/blob/master/test_check.c#L3256
        $binary  = hex2bin("8ce03cd60514233b86789729102ea09e867fc6d964dea8c2018ef7d0a2e0e24bf7e348e917116690b9");
        $keypair = KeyPair::create("abf4cf55a2b3f742d7543d9cc17f50447b969e6e06f5ea9195d428ab12b7318d");
        $signed  = $keypair->sign($binary); // leave second arg empty to use default algorithm (keccak-512)

        // check binary representation
        $this->assertEquals(64, strlen($signed->getBinary()));

        // check hexadecimal representation
        $expectHex = "d9cec0cc0e3465fab229f8e1d6db68ab9cc99a18cb0435f70deb6100948576cd5c0aa1feb550bdd8693ef81eb10a556a622db1f9301986827b96716a7134230c";
        $this->assertEquals(128, strlen($signed->getHex()));
        $this->assertEquals($expectHex, $signed->getHex());
    }

    /**
     * Unit test for *NEM Test Vectors* KeyPair signatures.
     * 
     * This uses data that can be found at [NEM Test Vectors](https://raw.githubusercontent.com/NemProject/nem-test-vectors)
     * 
     * @link https://raw.githubusercontent.com/NemProject/nem-test-vectors
     * @depends testBasicKeyPairSignIsKeccak512
     * @dataProvider nemTestVectorsProvider
     * @return void
     */
    public function testNEMVectorsKeyPairSignatures($privateKey, $data, $signatureHex)
    {
        $keypair = KeyPair::create($privateKey);
        $binary  = hex2bin($data);
        $signed  = $keypair->sign($binary);

        // check binary representation
        $this->assertEquals(64, $signed->getInternalSize());

        // check hexadecimal representation
        $this->assertEquals(128, strlen($signed->getHex()));
        $this->assertEquals($signatureHex, $signed->getHex());
    }

    /**
     * Data provider with data from NEM Test Vectors.
     * 
     * Each row of the returned array contains an array with 
     * 3 columns in following strict order:
     * 
     * - Private Key in hexadecimal format
     * - Data in hexadecimal format
     * - Signature in hexadecimal format
     * 
     * return array 
     */
    public function nemTestVectorsProvider()
    {
        return [
            ["6aa6dad25d3acb3385d5643293133936cdddd7f7e11818771db1ff2f9d3f9215",
             "e4a92208a6fc52282b620699191ee6fb9cf04daf48b48fd542c5e43daa9897763a199aaa4b6f10546109f47ac3564fade0",
             "98bca58b075d1748f1c3a7ae18f9341bc18e90d1beb8499e8a654c65d8a0b4fbd2e084661088d1e5069187a2811996ae31f59463668ef0f8cb0ac46a726e7902"],
            ["8e32bc030a4c53de782ec75ba7d5e25e64a2a072a56e5170b77a4924ef3c32a9",
             "13ed795344c4448a3b256f23665336645a853c5c44dbff6db1b9224b5303b6447fbf8240a2249c55",
             "ef257d6e73706bb04878875c58aa385385bf439f7040ea8297f7798a0ea30c1c5eff5ddc05443f801849c68e98111ae65d088e726d1d9b7eeca2eb93b677860c"],
            ["c83ce30fcb5b81a51ba58ff827ccbc0142d61c13e2ed39e78e876605da16d8d7",
             "a2704638434e9f7340f22d08019c4c8e3dbee0df8dd4454a1d70844de11694f4c8ca67fdcb08fed0cec9abb2112b5e5f89",
             "0c684e71b35fed4d92b222fc60561db34e0d8afe44bdd958aaf4ee965911bef5991236f3e1bced59fc44030693bcac37f34d29e5ae946669dc326e706e81b804"],
            ["09ddd185a0a2b62760ca35567b83ead845acef97ad2bca6bfea381ff8e806c1d",
             "55dd33145cb8cbfbd21264c2ff786066b21a2db7fe47f6e1410d20cc9e50fb9d6462188e4602a041",
             "66e519a2ba8f3eb16b63d69bf9d3be564dac0ca516d4ebec9bde5be34caf4fdc20a0a22ea45a01a00bc5f29016e35e128a7d73e4e7b396762c87f5c8f0695209"],
            ["030c0b139705180a44d5a2406fbdb92afb16f93c2d168dd39d2f5f43516295ec",
             "60a3512e600236957f271a5b47ec35ab8991a1f0a0b76564d0549b77357779d0b1502365dd83de78191265",
             "23fafe9934bd84b70ad9e4f453b756bc858627524e55be47c53ff41ae7cc3ed2520282781cf0281c0c00e81ddfad981abdaf0ccdc98a9cea2edf9d262a7c0f0d"],
            ["2900d96d21b7ace794558a432c1f28b806280ad93c31482e5b40a117e1219f21",
             "3b68a2de1c8808566eb969c032f5a10df91ab7ae2d3d57c5a6a3376572876e0d94a759eab785d943ad1da35689",
             "700f9e02632a6c751ca0a06156a55a3c8a1a6d0bc405b3e65f74a0e46fb5cdbbcf20865e0368f939e7482b5032d39f613d1e8b9c69f8e969bbcc2708d46a2207"],
            ["d7da4e94fb9925b6ab01370f26d45d1649a47b8601ec7cb8ab8e410fe8205d21",
             "2dee16a97075fdf715819fa1e88a49f96c7345f414330ab6bd5cb12a81412969920f0483961da4ea6adcbc01fdf5",
             "52199acf799d9defda5d3fcce7c696e5af7ed98c94b46b00dba5ea157f7d4ce78be29e0cea6de2b0e8299bf0110059f90683f12f67fd7733b40ae7ad5a945f0f"],
            ["3c3408d03b2c01fa5547375f73ca9734858da32bc6cfd7c62c184eeb49652f69",
             "51d01978437b34277ef148297e09197f5f03d434e57e1dd61b975bb2ed290b87b3dba4425b0b705be211426236",
             "5f97dd102b02a844ef9d3b4d8bbd3adeed42aba5ea84c02ca46cdd47ab5046b1ebf90f5a58860e8f14cae4dc84ec90bcc52bc1b1c213c998ddb73d5d6d3eb105"],
            ["725a04a1ba670b678b0c9162b53b550e9c0e0768dfea503d33951c1bcd22e6a1",
             "c610347d5fd368c7be675b8b225cc8c8f92444797a0dfad09e222d8d503ebe561e4dbefa5d5e1f6f71229f9955a2692527f6e8",
             "b5d842ac3019faeee1608ad23621fc1d24de87fcc32487e19cfc29d897cb8c8ebccc09fa0e28679732434a490b666cb8ccdb95d880da38ad692f53487664ec07"],
            ["c0fa590d5c7d85865f034e6ca954335104c362409b5bed31dfaceeac905e2bdf",
             "1a3d7ed3e11d9ee3ce2bf3811ba3b8c8f09d921048637998782d942fbd2bcf5bef60546fb6d4d9d06fe7e4",
             "f0e34eba9189be832f2a7284c29a8a9abab172aa0bd3ccf2f7bb16c3192beb5698a80715670c8f55f509f0e69d5cdc8ec01e3b8479a9d2ffe08b956b362dbb0d"],
            ["8df2ff08a48a298f26965f250b0bde196ab3c855ee73179792f5aa90ebe5ff75",
             "b73ea07c4575cf18203804ce0fd1dde62d9ec5429668b702567613d15bc42c5951343a3fb39a6cf2dd6140d2f6a0140826d9",
             "8e2c4018d98245d3812dc53b36f28c2f1a919fd6709405539df7f80aeab7a1938389fe636c869baa39b200dab1d03d44d125568105f1fd5355f39d3d70915302"],
            ["ff50388c443fc85604011ba7a17faa95ff3a5a96cd4c9c1d4195022ab95606b5",
             "18b45060df3b3e007a848e0c10ff21dd711ec6d82374795ce78737c8b59d5ea181647445710f29772caebd",
             "8cf73a3c4022435b44fe0a3e1132acb2e7ae4a5a2ab6a0670846394ae48ef8210788e201e34b99fec2ced20eaeef622d035df5eda0c88d04c7f761d5e9201c0f"],
            ["3b497d0ac2d35948598bbad79fe51f85f252a374ebac960a981f2df9a2192011",
             "b8421057fbff0ada3b0061022582cae5ce1fd4dc5cff6a85e69c6842631732d395637ee1bf10d8492ca5d18d28a451a297aa6c6b",
             "efd5b113c2bca7af15ef7c3187da376deb364342ed89fe9937e28236e82cd79f311c3486f2511a3d82ee01a606154c32e76a5d1d4b596a2ed5c99dd51e3bfd01"],
            ["6c3fc791b934a27c9ba56fccf6686b119cb6d7581d5d57f9487bed45fcb13421",
             "b70d970bbd9d5c45652ca7e16ece269217eb0802f17076ea601b08459d35704c8df73ab929855f26fe9d893af5",
             "2d287c73c20d62ccc21f1906148c3b0b8f9ba214bc65a48586d81d1b627bf4874edc8442dd2a214f936a5645ebc65c42720f2a016ca7cb16fae9f5b87c5d9308"],
            ["917346c56ae2a44fc75a482153aa6bd64e39a057fbd5a66348e8f225d11028ef",
            "b467b4c713729909a156fa890703bf8cc8bbc0e2ab1b040c579df19e44e5842249a94c8a62bb8395a3d941bc1e",
            "23ecb5f84a77757a38a24c783ed5baa547cc156a0a5f1483d1a20e73238975968fbc1761f101a9e354920513e165bb270746eea429fa9f2b1e9d573bc36d180f"],
            ["56bfe9dd9fc1d59ef8b2d076e461a3caa87f8735844b3552ef4009d4ec878feb",
             "4f4bb27e991d96a6ebbea2d50a25b2c92bfce630aba3fe555525844ee898dc005cbcbc394716bb8fdf",
             "8afa4ebe1990b5cbb3a72c694b25ed425be21e97fe2fe8a858c80b980bfc991577a59f1eae92baec93bbc543a88ba28cee0d9fa849af71accf68243cd0d40608"],
            ["1de73b20a1b47f076dfebc14845113c7478b47aae10c80121c8cb5896c208bfa",
            "98ba30d1553626ffcafde332b5b319669f80540f4b8aaaa242e7e12c524cd2d08683e6b2a0dcbea17d1dd9cf5aaf",
            "756d9ae430e87863fefcf667da4b77e1086f26291d39f60c94d5cb8d81956e39d65adc967fabf6c38f37d3b4c16a5d00f67d2386c3932b1249eb9254bf0aed01"],
            ["238df94cb6215ef79a174b58f801e45a1223774610059a776d09ea30ee8abc06",
             "98fd297317d550022e457e988bf001bea6088db03bce0203c300d96a18d2a2fb7cb5729c11d90b8d",
             "6478765c98979318d26ebefbcffe590b389e4a0f1400ba4ef8ebc652e7779c8e8aea03ca06ce0aab5a35d795415b9f791619be1ae6c0957550f8b6a0dd71d30d"],
            ["b819151bd594fc20a012f17065e19139bce5864d70bc9c25984f1704a90e4d9e",
            "fe30dc11ea8a35a3011a55a415a5743127f4821b22ecd47c39517590e2178ae22268aafb64465a26d5e53c550c43594f9e337efe",
            "9bf3703377f73e4757df874ca9f65b8bfb3565fd2abf49edaa7942921fbdd23d5801a1e97be14799217130f795191563c89b546eeceef2bdb456a5699c7af104"],
            ["5e83d01e3ff9d6a4b6a5520c0c66e754de90ab12159a8108fa74d90dfdcab586",
             "b486e9a4c1929e2fe8da74538d3fdc2270704b482ff709d293c168733c013455408d0dda6c5763ad5e5cdfc0b3d699d05662",
             "3e8ee8d2275a50c4ce0a2af61fe6d92a3d582c106484cf661272b396c2fcc89d8d3ee98c6f0c8bbcff7a08648748a2bfc28ca3f10950ee42d05768c31e8d6b09"],
            ["0105e06b9f2ec3b9af6a85639838ce65cb5828f8f458396098276399db609eac",
            "fd5d4227069c41cb7d49a82ff2e7d97e3ee701be94d620db7b6b70203d3e1b152b3b5453998c9d81bb9a3047b7ea4b",
            "786e8e4f3659528de612581f7784b3d2f088046024bea98040a5b25721748d3598a3297bf0f6f7215486be34ade73700c5703e9b60802c9ac5beb5e23ed5f70b"],
            ["fc62383b544a620b6fc0241f7c63112a400bed22f20c5098602e9920b58c8d32",
             "5531d802f4cd2204899018dcdf36e1fb5663dbc7e980e55c9bc46af19ca87cb286c5283d46692202",
             "a027f2fd1df9dcac33b8b4978d98ee44f0adce4d12b273be0f17303ee20ba79f697185c86e4f22b861f55ec8d68544e2b5bc43a88ad484f1a2f578683c62bc03"],
            ["cdf8a7181533a5183502415ec18b060e8fe104a27a7365cb547757662de712e0",
            "3563c2f790d5c42d222a0f45069a52afa2139e86ed3b0c6baba2c00bdd1a7a78fcfcd85414db0903e461",
            "174b74f09a66286aadef62d4a09c2d43051e4d31682639ac7583b0ee787b8dfb3b7a15d5460ff6f6e197b32996d52531e0fa820a177b4d9707b5854fe3dba804"],
            ["5d60b07b994e300009c889042b86349a98dbd1fd95e24189375294d15893407a",
             "22ec9cb38dc660224772b7f546d35f31833d67ac44dd393233b0c843de08a22198de3b79db2aa3b7cf54b43d2d72",
             "41d0584e9c5eadbe35a27369e7e1d303a47a5e80357f26cef91ded823e2469522fffe99de55b318f9ead22e9b498b159507bc4161d87b8f7cd11ef19f7ea350e"],
            ["5eda792761e6c9a5ec0d9fbeea99f539db2e216de00883ee058c93050d0079e8",
            "0e2c87686394ed0a658905209a2ba8e1df6493a1bba9540c4a35c518417ba5e6365ad7214a95637afc0b",
            "eac1589eb9e02508f5ccd3682e40f48c924b0e8f7b5b40b91bf5b75cbc9cb4039f140cc3a2941b2ef4404b3c6cac4f5b7cfa494e2c013039cb5e8f3b3a6d630d"],
            ["0213e370c5fffef56ca6ea4fed90280a505384661059b30b88e36f2d4cfe91ed",
             "6b2f5a35f82f4de39d54b2122b228b45c113aef6fa74fec55e9aaa5fb0281fdae05e6678f16b0cd1aa191fff",
             "693205dbfa7285fbeb8835c6d843babfed202d37a71727c5eccde4b411228b87eaadbd7efb93103227fa35a57ab4273183e19ea49712e144da9f2119ad917804"],
            ["111ab54022f0283bc2868f4e3353151e64da9dbcf95b9f2002993c71499d1ba0",
            "282f213f61679386eba0b843f5316d3066ff66b4352e98e4a78af6dce4d49323267e1e8b9ecfee617108631609252228cea6",
            "412f9b5de8da519ccba2af70b8be3a6b599d752a2c4b262fab38209caa06b732406ad8c4f18efa0d1b925375bb842f47618479a6ba38602c4ee718344fba1a0f"],
            ["a79d54f260766fa081064f54982f27fd53f9648724fe69843792ed8a39364614",
             "5748ef40689fb175c844c4832bcf103b210bcbbcd21c62803b2863f0a84c7e8a846298546ce5128615fed4c129fa85ef26",
             "6cc60f7e4f3cf000bd9532cab9364af41ed3e1f11491f69f022a2b972f3cdb974d42f4796a5b691d57882bc23e1d47e105c75b306ae8f59ace001c1114fea708"],
            ["b52b609ae7d992a8053e678cf20194d36a4b23e920777a2bfdd0833f87fe8ba5",
            "79e0d281e16e11c42c9e56ad2423e36e45c18b272196c041f765afe9a86ddda4afdb527233981314b5d3c43f",
            "e987a1c013275a6639a9b6f9842eaab2ab2b46bd60ff4221a5ab70c9dd91740b668b27fd6edd4057da96135a12160fcd0d1ac92ce0799e753736e1aa6d1ca20c"],
            ["aebf87e0f26f096632d44aa3fe645882e8a656661ef9e93dd9f9e94d02cc688f",
             "92561a5d385cb9fca28d1f2ce79ec797fb819d10a93fa7db2514bf1d76d5fcc6cff7e8ff620a02efc08999ecf728",
             "8bc0d6c2346bc69a733429c837156bd41e88186ef5a6bf06259b90057f1be6c843596aa6c74a7449bd26426253905a140ee6f357d88cbbfc9082acccc6f53f0c"],
            ["8319f618605679cded15cf7930da38fa1f9c4f89e0df26000dc61ec2a02f7fb7",
            "2829f449cc97d0ec1434925d35d419f138b3a83e9f92048965b1f5d0e7e38618c86d55a7850205f6dbe691",
            "eeb072a7589f2aa5f170cc1389ac26debc549864b17528b1605c09c649c97124014745c76af0a9cf01dbb3ae8ba6e8f86532cb37eab3b4ddbae01e5dc616f202"],
            ["7758b44c1dc8826452899768d6ee9c8af269315c9be4a0b2ec7b428a01e3bc4b",
            "89d8575ad83364b08e73e37e709050c85a81631f43a11830bff6aef34989b6df6911533e62795c7d185a",
            "2492855b5cc4e23c4f8450b6640413f4a4fe67aafd22fa65ad8fd15a8f12baf4e6c36ba50110819c8db473a672db296508a9a957b8865dc79c1b05d7c8a2640a"],
            ["7e3096336ae4c5f87b644cb86c646008acf636c0f82ca8294659eedf92bd2ec7",
            "7408746ba5e76e6b18480b46eab6ebe1532602ec464158647d2561fe99c7d3ff382a2a47e68da1f639dd",
            "244fcad0524fa88d331e696056aa2bd14dac3955d0c6f2db5ec70afc417e747515ec3ec90d03e915b6261b0c16d01292b41830c6797240a3b3f4ecae6834e600"],
            ["4afa8e71a56046fe2bfd0c8974685f98bd66155d98400da23a967b3fbd3f28b6",
            "5b4eb881bb94fd72fcb517a9ea4bf4f0fc78eb31c9781e9126e63a7a0ddfc0501d45ca6990bbb34557bc821bc92db547",
            "a67dea6619958148fe5a1352c01983bb8ac659032b1aef9cce9221b406bfdc861d2853e34a0d53b25e02d002787b19577ccafcb77bcbc093fb46749905d9640c"],
            ["4afa8e71a56046fe2bfd0c8974685f98bd66155d98400da23a967b3fbd3f28b6",
            "f4aade82583c842336d34d7579b6d68b9344ef81ce991427485f61e034c626d821fcdf2af87a216518ee",
            "b0bc9c44fc9719709bd425d8b3a67893e5d473687ecca51ce3254b6c9eb8866affd036ac1acf004faf28694cc4ae0bdab3ffe588ff2438c3367ae020e5d5cc04"],
            ["4afa8e71a56046fe2bfd0c8974685f98bd66155d98400da23a967b3fbd3f28b6",
            "bce51e2ac2b85d62886dc59d97be352e47ebc63e62324930e8c33d9646fd5eecd8561de3385ddca2c89824d018b0da",
            "bd4a7d517fb3277da36c2021bbc28a0b6bf4a62b37a6f78471b05821b44f22ecd4f1000907d1185b1e99f3a74dc8764f785ca28b4677842f7e608ea4dd6df609"],
            ["4afa8e71a56046fe2bfd0c8974685f98bd66155d98400da23a967b3fbd3f28b6",
            "942e2eb1bae4e5cc0c5ee589fc730956db24900447a0dd9b7c05a86c24e84d87d7da6db2bc52e7ecaf5f711fbd",
            "7e0ed4c1d1ebcff49abaa6eb998b831d492d2b17031168264fc8f9f88423832acf16bbac85305dd33bece4a8600b203c1dc380ca8a4f66cc364bfb662d682a06"],
        ];
    }
}
