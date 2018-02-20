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

class DebugTest
    extends TestCase
{
    /**
     * Unit Test for `__debugInfo` overload in
     * Nice\Crypto\Buffer.
     *
     * @return void
     */
    public function testDebugOutput()
    {
        $buffer = new Buffer('AAAA', 4);
        $debug = $buffer->__debugInfo();
        $this->assertTrue(isset($debug['buffer']));
        $this->assertTrue(isset($debug['size']));

        $str = $debug['buffer'];
        $this->assertEquals('0x', substr($str, 0, 2));
        $this->assertEquals('41414141', substr($str, 2));
    }
}
