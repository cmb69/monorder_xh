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

    public function dataForMessage()
    {
        return array(
            array('success', ''),
            array('info', ''),
            array('warning', 'cmsimplecore_warning'),
            array('fail', 'cmsimplecore_warning')
        );
    }

    /**
     * @dataProvider dataForMessage
     */
    public function testMessage($type, $class)
    {
        $message = 'foo bar';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => $class),
            'content' => $message
        );
        $actual = $this->_subject->message($type, $message);
        $this->assertTag($matcher, $actual);
    }

    public function testSystemCheck()
    {
        $checks = array('everything' => 'ok');
        $matcher = array(
            'tag' => 'ul',
            'children' => array(
                'count' => 1,
                'only' => array('tag' => 'li')
            )
        );
        $actual = $this->_subject->systemCheck($checks);
        $this->assertTag($matcher, $actual);
    }

    public function testAboutShowsVersionInfo()
    {
        define('MONORDER_VERSION', 'foobar');
        $matcher = array('tag' => 'p', 'content' => 'Version: foobar');
        $actual = $this->_subject->about();
        $this->assertTag($matcher, $actual);
        runkit_constant_remove('MONORDER_VERSION');
    }

    public function testAboutShowsPluginIcon()
    {
        define('MONORDER_VERSION', 'foobar');
        $this->_model->expects($this->once())
            ->method('logoPath')
            ->will($this->returnValue('foobar'));
        $icon = 'icon.png';
        $matcher = array(
            'tag' => 'img',
            'attributes' => array('src' => 'foobar')
        );
        $actual = $this->_subject->about();
        $this->assertTag($matcher, $actual);
        runkit_constant_remove('MONORDER_VERSION');
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
        $actual = $this->_subject->itemList('/');
        $this->assertTag($matcher, $actual);
    }

    public function testItemFormHasForm()
    {
        $matcher = array('tag' => 'form');
        $this->_model->expects($this->once())
            ->method('availableAmountOf')
            ->will($this->returnValue(42));
        $actual = $this->_subject->itemForm('foo', '/');
        $this->assertTag($matcher, $actual);
    }

    public function testInventoryShowsAvailableAmount()
    {
        global $plugin_tx;

        $expected = 42;
        $plugin_tx['monorder']['free_plural'] = '%d';
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