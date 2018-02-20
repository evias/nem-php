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
namespace NEM\Tests\SDK\Buffer;

use NEM\Core\Buffer;
use NEM\Tests\TestCase;
use Mdanter\Ecc\EccFactory;

class SerializeTest
    extends TestCase
{
    /**
     * Unit Test for `__debugInfo` overload in 
     * Nice\Crypto\Buffer.
     *
     * @return void
     */
    public function testSerialize()
    {
        $hex = '41414141';
        $dec = EccFactory::getAdapter()->hexDec($hex);
        $bin = pack("H*", $hex);
        $buffer = Buffer::fromHex($hex);

        // Check Binary
        $retBinary = $buffer->getBinary();
        $this->assertSame($bin, $retBinary);

        // Check Hex
        $this->assertSame($hex, $buffer->getHex());

        // Check Decimal
        $this->assertSame($dec, $buffer->getInt());
        $this->assertInstanceOf(\GMP::class, $buffer->getGmp());
    }
}
