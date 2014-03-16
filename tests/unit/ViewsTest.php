<?php

require_once './classes/Model.php';
require_once './classes/Views.php';

class ViewsTest extends PHPUnit_Framework_TestCase
{
    private $_model;

    private $_subject;

    public function setUp()
    {
        $this->_model = $this->getMockBuilder('Monorder_Model')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_subject = new Monorder_Views($this->_model);
    }

    public function testItemListHasCorrectNumberOfListItems()
    {
        $matcher = array(
            'tag' => 'ul',
            'children' => array(
                'count' => 3,
                'only' => array('tag' => 'li')
            )
        );
        $this->_model->expects($this->once())
            ->method('items')
            ->will($this->returnValue(array('foo', 'bar')));
        $actual = $this->_subject->itemList();
        $this->assertTag($matcher, $actual);
    }
}