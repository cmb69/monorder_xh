<?php

/**
 * The model class.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Monorder
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Monorder_XH
 */

/**
 * The model class.
 *
 * @category CMSimple_XH
 * @package  Monorder
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Monorder_XH
 *
 * @todo handle fopen() failures
 */
class Monorder_Model
{
    /**
     * The path of the data file.
     *
     * @var string
     */
    private $_filename;

    /**
     * The item data as map from item name to available amount.
     *
     * @var array
     */
    private $_items;

    /**
     * The stream handle for locking during ::reserve()/::commit() cycle.
     *
     * @var resource
     */
    private $_stream;

    /**
     * Initializes a new instance.
     *
     * @global array The paths of system files and folders.
     */
    public function __construct()
    {
        global $pth;

        $this->_filename = $pth['folder']['plugins'] . 'monorder/data/monorder.dat';
        if (!file_exists($this->_filename)) {
            $this->createDataFile();
        }
    }

    /**
     * Returns the number.
     *
     * @param int $amount An amount.
     *
     * @return string
     */
    function number($amount)
    {
        if ($amount == 1) {
            $result = 'singular';
        } elseif ($amount >= 2 && $amount <= 4) {
            $result = 'paucal';
        } else {
            $result = 'plural';
        }
        return $result;
    }

    /**
     * Returns the path of the data file.
     *
     * @return string
     */
    public function filename()
    {
        return $this->_filename;
    }

    /**
     * Creates a new data file.
     *
     * @return void
     */
    protected function createDataFile()
    {
        $foldername = dirname($this->_filename);
        if (!file_exists($foldername)) {
            mkdir($foldername, 0777, true);
        }
        $stream = fopen($this->_filename, 'a');
        flock($stream, LOCK_EX);
        fseek($stream, 0);
        ftruncate($stream, 0);
        fwrite($stream, serialize(array()));
        flock($stream, LOCK_UN);
        fclose($stream);
    }

    /**
     * Clears the item cache.
     *
     * @return void
     */
    public function clearCache()
    {
        $this->_items = null;
    }

    /**
     * Returns the item data as map from item name to available amount.
     *
     * @return array
     */
    public function items()
    {
        if (!isset($this->_items)) {
            $stream = fopen($this->_filename, 'r');
            flock($stream, LOCK_SH);
            $this->_items = unserialize(stream_get_contents($stream));
            flock($stream, LOCK_UN);
            fclose($stream);
        }
        return $this->_items;
    }

    /**
     * Returns whether a certain item exists.
     *
     * @param string $name An item name.
     *
     * @return bool
     */
    public function hasItem($name)
    {
        $items = $this->items();
        return isset($items[$name]);
    }

    /**
     * Returns the available amount of an item.
     *
     * @param string $item An existing item name.
     *
     * @return int
     */
    public function availableAmountOf($item)
    {
        assert($this->hasItem($item));

        $items = $this->items();
        return $items[$item];
    }

    /**
     * Returns whether an item is available.
     *
     * @param string $item An existing item name.
     *
     * @return bool
     */
    public function isAvailable($item)
    {
        return $this->availableAmountOf($item) > 0;
    }

    protected function modify($modifier)
    {
        $stream = fopen($this->_filename, 'r+');
        flock($stream, LOCK_EX);
        $this->_items = unserialize(stream_get_contents($stream));
        $modifier();
        fseek($stream, 0);
        ftruncate($stream, 0);
        fwrite($stream, serialize($this->_items));
        flock($stream, LOCK_UN);
        fclose($stream);
    }

    /**
     * Sets the available amount of an item.
     *
     * @param string $item   An item name.
     * @param int    $amount An amount.
     *
     * @return void
     */
    public function setItemAmount($item, $amount)
    {
        assert($amount >= 0);

        $this->modify(
            function () use ($item, $amount) {
                $this->_items[$item] = $amount;
            }
        );
    }

    /**
     * Adds a new item.
     *
     * @param string $name An item name.
     *
     * @return void
     */
    public function addItem($name)
    {
        assert(!$this->hasItem($name));

        $this->setItemAmount($name, 0);
    }

    /**
     * Removes an item.
     *
     * @param string $name An item name.
     *
     * @return void
     */
    public function removeItem($name)
    {
        assert($this->hasItem($name));

        $this->modify(
            function () use ($name) {
                unset($this->_items[$name]);
            }
        );
    }

    /**
     * Reserves a certain amount of items and returns whether that succeeded.
     *
     * @param string $item   An existing item name.
     * @param int    $amount An amount.
     *
     * @return bool
     */
    public function reserve($item, $amount)
    {
        assert($this->hasItem($item));
        assert($amount > 0);

        $this->_stream = fopen($this->_filename, 'r+');
        flock($this->_stream, LOCK_EX);
        $this->_items = unserialize(stream_get_contents($this->_stream));
        if ($amount <= $this->_items[$item]) {
            $result = true;
            $this->_items[$item] -= $amount;
        } else {
            $result = false;
            flock($this->_stream, LOCK_UN);
            fclose($this->_stream);
            $this->_stream = null;
        }
        return $result;
    }

    /**
     * Commits the current reservation.
     *
     * @return void
     */
    public function commitReservation()
    {
        assert(isset($this->_stream));

        fseek($this->_stream, 0);
        ftruncate($this->_stream, 0);
        fwrite($this->_stream, serialize($this->_items));
        flock($this->_stream, LOCK_UN);
        fclose($this->_stream);
        $this->_stream = null;
    }
}

?>
