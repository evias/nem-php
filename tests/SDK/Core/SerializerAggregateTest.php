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
use NEM\Core\Serializer;
use NEM\Core\Buffer;

class SerializerAggregateTest
    extends TestCase
{
    /**
     * Unit test for *aggregate serialized objects* action.
     * 
     * @return void
     */
    public function testSerializedDataAggregator()
    {
        $serializer = Serializer::getInstance();

        $one = $serializer->serializeUint8([1, 0, 0, 0]);
        $two = $serializer->serializeUint8([2, 0, 0, 0]);
        $aggregated = $serializer->aggregate($one, $two);

        $len1 = count($one); // 7
        $len2 = count($two); // 7

        // aggregate should prepend size on 4 bytes
        $expectSize = 4 + $len1 + $len2;
        $expectUInt8 = [
            16, 0, 0, 0,
            4, 0, 0, 0, 1, 0, 0, 0, 
            4, 0, 0, 0, 2, 0, 0, 0, 
        ];

        $this->assertEquals($expectSize, count($aggregated));
        $this->assertEquals(json_encode($expectUInt8), json_encode($aggregated));
    }
}
