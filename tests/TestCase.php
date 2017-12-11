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
namespace NEM\Tests;

use PHPUnit\Framework\TestCase as BaseTest;
use Mockery;

abstract class TestCase 
    extends BaseTest
{
    /**
     * Setup unit test cases
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Close the mockery operator.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }
}
