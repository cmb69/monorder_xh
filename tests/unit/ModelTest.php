<?php

require_once 'vfsStream/vfsStream.php';

require_once './classes/Model.php';

class ModelTest extends PHPUnit_Framework_TestCase
{
    private $_subject;

    public function setUp()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('plugins'));
        runkit_function_redefine('ftruncate', '$s, $p', '');
        $pth['folder']['plugins'] = vfsStream::url('plugins/');
        $this->_subject = new Monorder_Model();
    }

    public function testCustomDataFolder()
    {
        global $pth, $plugin_cf;

        $pth['folder']['base'] = vfsStream::url('plugins/');
        $plugin_cf['monorder']['folder_data'] = 'userfiles';
        $subject = new Monorder_Model();
        $expected = $pth['folder']['base'] . 'userfiles/monorder.dat';
        $this->assertEquals($expected, $subject->filename());
    }

    public function testNumber()
    {
        $this->assertEquals('plural', $this->_subject->number(0));
        $this->assertEquals('singular', $this->_subject->number(1));
        $this->assertEquals('paucal', $this->_subject->number(2));
        $this->assertEquals('paucal', $this->_subject->number(4));
        $this->assertEquals('plural', $this->_subject->number(5));
    }

    public function testLogoPath()
    {
        global $pth;

        $expected = $pth['folder']['plugins'] . 'monorder/monorder.png';
        $actual = $this->_subject->logoPath();
        $this->assertEquals($expected, $actual);
    }

    public function testCorrectFilename()
    {
        global $pth;

        $expected = $pth['folder']['plugins'] . 'monorder/data/monorder.dat';
        $actual = $this->_subject->filename();
        $this->assertEquals($expected, $actual);
    }

    public function testAmountProperlySet()
    {
        $itemName = 'foo';
        $expected = 42;
        $this->_subject->setItemAmount($itemName, $expected);
        $actual = $this->_subject->availableAmountOf($itemName);
        $this->assertEquals($expected, $actual);
    }

    public function testAvailabilityIsProperlyReported()
    {
        $itemName = 'foo';
        $this->_subject->setItemAmount($itemName, 42);
        $this->assertTrue($this->_subject->isAvailable($itemName));
        $this->_subject->setItemAmount($itemName, 0);
        $this->assertFalse($this->_subject->isAvailable($itemName));
    }

    public function testExistsOnlyBetweenAddingAndRemoving()
    {
        $itemName = 'foo';
        $this->assertFalse($this->_subject->hasItem($itemName));
        $this->_subject->addItem($itemName);
        $this->assertTrue($this->_subject->hasItem($itemName));
        $this->_subject->removeItem($itemName);
        $this->assertFalse($this->_subject->hasItem($itemName));
    }

    public function testAbortedReservation()
    {
        $itemName = 'foo';
        $this->_subject->setItemAmount($itemName, 17);
        $this->_subject->reserve($itemName, 4);
        $this->assertTrue($this->_subject->reservationInProgress());
        $this->_subject->rollbackReservation();
        $this->_subject->clearCache();
        $actual = $this->_subject->availableAmountOf($itemName);
        $this->assertEquals(17, $actual);
    }

    public function testCommittedReservation()
    {
        $itemName = 'foo';
        $this->_subject->setItemAmount($itemName, 17);
        $this->_subject->reserve($itemName, 4);
        $this->assertTrue($this->_subject->reservationInProgress());
        $this->_subject->commitReservation();
        $this->_subject->clearCache();
        $actual = $this->_subject->availableAmountOf($itemName);
        $this->assertEquals(13, $actual);
    }

    public function testProhibitsOverbooking()
    {
        $itemName = 'foo';
        $this->_subject->setItemAmount($itemName, 42);
        $actual = $this->_subject->reserve($itemName, 43);
        $this->assertFalse($actual);
    }
}

?>
