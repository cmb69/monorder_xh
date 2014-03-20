<?php

require_once './classes/Controller.php';
require_once './advancedform.php';

class AdvancedformTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $plugin_cf, $_Monorder;

        $plugin_cf['monorder']['advancedform_item_field'] = 'order_item';
        $plugin_cf['monorder']['advancedform_amount_field'] = 'order_amount';
        $_Monorder = $this->getMockBuilder('Monorder_Controller')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDefaultValueForOrderItem()
    {
        global $_Monorder;

        $_Monorder->expects($this->once())
            ->method('currentItem')
            ->will($this->returnValue('foo'));
        $expected = 'foo';
        $actual = advfrm_custom_field_default('', 'order_item', '', false);
        $this->assertEquals($expected, $actual);
    }

    public function testDefaultValueForOtherField()
    {
        $actual = advfrm_custom_field_default('', 'foo', '', false);
        $this->assertNull($actual);
    }

    public function testValidationOfOrderItem()
    {
        global $_Monorder;

        $_Monorder->expects($this->exactly(2))
            ->method('currentItem')
            ->will($this->returnValue('foo'));
        $actual = advfrm_custom_valid_field('', 'order_item', 'foo');
        $this->assertTrue($actual);
        $actual = advfrm_custom_valid_field('', 'order_item', 'bar');
        $this->assertNull($actual);
    }

    public function testValidationOfOtherField()
    {
        $actual = advfrm_custom_valid_field('', 'foo', 'bar');
        $this->assertTrue($actual);
    }

    public function testOrdering()
    {
        global $_Monorder;

        $_Monorder->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue(true));
        $_Monorder->expects($this->once())
            ->method('commitReservation');
        $actual = advfrm_custom_valid_field('', 'order_amount', '42');
        $this->assertTrue($actual);
        advfrm_custom_mail('', $_Monorder, false);
    }
}

?>
