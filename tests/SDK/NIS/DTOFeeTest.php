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

use NEM\Models\Amount;
use NEM\Models\Fee;
use NEM\Models\Message;
use NEM\Models\Mosaic;
use NEM\Models\MosaicAttachments;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicDefinitions;

class DTOFeeTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *empty message fee calculation*.
     * 
     * @return void
     */
    public function testMessageFeeEmpty()
    {
        $message = new Message();
        // leave $message empty!

        $expectFee = 0;
        $actualFee = Fee::calculateForMessage($message);

        $this->assertEquals($expectFee, $actualFee);
    }

    /**
     * Unit test for *dynamic fee calculation* related to message size.
     * 
     * @return void
     */
    public function testMessageFeeDynamicUnencrypted()
    {
        $messageLess  = new Message(["plain" => "message with less than 31 char"]);
        $messageEqual = new Message(["plain" => "message with exactly 31 chars.."]);
        $messageMore  = new Message(["plain" => "message with more than 31 chars."]);

        // calculate
        $feeLess  = Fee::calculateForMessage($messageLess);
        $feeEqual = Fee::calculateForMessage($messageEqual);
        $feeMore  = Fee::calculateForMessage($messageMore);

        // content validation
        $this->assertNotEmpty($feeLess);
        $this->assertNotEmpty($feeEqual);
        $this->assertNotEmpty($feeMore);

        // test dynamicity of fee calculation with 31-characters bundles sizes
        $this->assertEquals($feeLess, $feeEqual);
        $this->assertEquals($feeLess * 2, $feeMore);
    }

    //XXX public function testMessageFeeDynamicEncrypted

    /**
     * Unit test for *XEM fee calculation* with empty XEM amount.
     * 
     * @return void
     */
    public function testXEMFeeMinimum()
    {
        $amount = new Amount();
        // leave $amount empty!

        // amount empty means `1 x FEE_FACTOR`
        $expectFee = 1;
        $actualFee = Fee::calculateForXEM($amount->toMicro());

        $this->assertEquals(0, $amount->toMicro());
        $this->assertEquals($expectFee, $actualFee);
    }

    /**
     * Unit test for *XEM fee maximum* amount.
     * 
     * @return void
     */
    public function testXEMFeeMaximum()
    {
        // sending 250000 XEM reaches the maximum fee
        $amount = new Amount(["amount" => 260*1000]);

        $expectFee = Fee::MAX_AMOUNT_FEE;
        $actualFee = Fee::calculateForXEM($amount->toMicro());

        $this->assertEquals(260000, $amount->toMicro());
        $this->assertEquals($expectFee, $actualFee);
    }

    /**
     * Unit test for *XEM fee calculation* with filled XEM amount.
     * 
     * @dataProvider xemFeeComputationVectorsProvider
     * @return void
     */
    public function testXEMFeeComputation()
    {
        // sending 250000 XEM reaches the maximum fee
        $amount = new Amount(["amount" => 260*1000]);

        $expectFee = Fee::MAX_AMOUNT_FEE;
        $actualFee = Fee::calculateForXEM($amount->toMicro());

        $this->assertEquals(260000, $amount->toMicro());
        $this->assertEquals($expectFee, $actualFee);
    }

    /**
     * Data provider for the testXEMFeeComputation() unit
     * test.
     * 
     * @return array
     */
    public function xemFeeComputationVectorsProvider()
    {
        return [
            /**
             * Column 1: XEM Amount
             * Column 2: Expected Fee factor
             */

            [1,         1],
            [2,         1],
            [10,        1],
            [20*1000,   2],
            [30*1000,   3],
            [100*1000,   10],
            [240*1000,   24],
            [500*1000,   Fee::MAX_AMOUNT_FEE],
        ];
    }

    /**
     * Unit test for *empty mosaic attachments fee calculation*.
     * 
     * @return void
     */
    public function testMosaicAttachmentsFeeEmpty()
    {
        $definitions = MosaicDefinitions::create();
        $attachments = new MosaicAttachments();
        // leave $attachments empty!

        $expectFee = 0;
        $actualFee = Fee::calculateForMosaics($definitions, $attachments);

        $this->assertEquals($expectFee, $actualFee);
    }

    /**
     * Unit test for *mosaic attachments minimum fee calculation*.
     * 
     * @return void
     */
    public function testMosaicAttachmentsFeeMinimum()
    {
        $attachment  = new MosaicAttachment([
            "mosaicId" => (new Mosaic([
                "namespaceId" => "nem",
                "name" => "xem"]))->toDTO(),
            "quantity" => 1
        ]);

        $definitions = MosaicDefinitions::create();
        $attachments = new MosaicAttachments([$attachment]);

        $expectFee = 1 * Fee::FEE_FACTOR;
        $actualFee = Fee::calculateForMosaics($definitions, $attachments);

        $this->assertEquals($expectFee, $actualFee);
    }

    /**
     * Unit test for *mosaic attachments fee calculation*.
     * 
     * @return void
     */
    public function testMosaicAttachmentsFeeCalculation()
    {
        $attachment  = new MosaicAttachment([
            "mosaicId" => (new Mosaic([
                "namespaceId" => "nem",
                "name" => "xem"]))->toDTO(),
            "quantity" => 100 * pow(10, 6)
        ]);

        $definitions = MosaicDefinitions::create();
        $attachments = new MosaicAttachments([$attachment]);

        // multiplier changes
        $actualFee_1 = Fee::calculateForMosaics($definitions, $attachments, 1 * pow(10, 6));
        $actualFee_2 = Fee::calculateForMosaics($definitions, $attachments, 1000 * pow(10, 6));

        $expectFee_1 = 1 * Fee::FEE_FACTOR;
        $expectFee_2 = 10 * Fee::FEE_FACTOR;

        $this->assertEquals($expectFee_1, $actualFee_1);
        $this->assertEquals($expectFee_2, $actualFee_2);
    }
}
