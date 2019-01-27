<?php

require_once '../../cmsimple/classes/CSRFProtection.php';
require_once './classes/Model.php';
require_once './classes/Views.php';

class ViewsTest extends PHPUnit_Framework_TestCase
{
    private $_model;

    private $_subject;

    public function setUp()
    {
        global $_XH_csrfProtection;

        $this->_model = $this->getMockBuilder('Monorder_Model')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_subject = new Monorder_Views($this->_model);
        $_XH_csrfProtection = $this->getMockBuilder('XH_CSRFProtection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInventoryShowsAvailableAmount()
    {
        global $plugin_tx;

        $expected = 42;
        $plugin_tx['monorder']['avail_plural'] = '%d';
        $this->_model->expects($this->once())
            ->method('availableAmountOf')
            ->will($this->returnValue($expected));
        $this->_model->expects($this->once())
            ->method('number')
            ->will($this->returnValue('plural'));
        $actual = $this->_subject->inventory('foo');
        $this->assertEquals($expected, $actual);
    }
}