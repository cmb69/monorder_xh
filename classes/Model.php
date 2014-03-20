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
     * The temporary item name needed for callbacks.
     *
     * @var string
     */
    private $_tempItem;

    /**
     * The temporary amount needed for callbacks.
     *
     * @var int
     */
    private $_tempAmount;

    /**
     * Initializes a new instance.
     *
     * @global array The paths of system files and folders.
     */
    public function __construct()
    {
        global $pth, $plugin_cf;

        if ($plugin_cf['monorder']['folder_data']) {
            $this->_filename = $pth['folder']['base']
                . trim($plugin_cf['monorder']['folder_data'], '/') . '/';
        } else {
            $this->_filename = $pth['folder']['plugins'] . 'monorder/data/';
        }
        $this->_filename .= 'monorder.dat';
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
    public function number($amount)
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
     * Returns the path of the plugin logo.
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     */
    public function logoPath()
    {
        global $pth;

        return $pth['folder']['plugins'] . 'monorder/monorder.png';
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
     *
     * @throws RuntimeException
     */
    protected function createDataFile()
    {
        $foldername = dirname($this->_filename);
        if (!file_exists($foldername)) {
            mkdir($foldername, 0777, true);
        }
        $stream = fopen($this->_filename, 'a');
        if ($stream) {
            flock($stream, LOCK_EX);
            fseek($stream, 0);
            ftruncate($stream, 0);
            fwrite($stream, serialize(array()));
            flock($stream, LOCK_UN);
            fclose($stream);
        } else {
            throw new RuntimeException("Can't write {$this->_filename}", 2);
        }
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
     *
     * @throws RuntimeException
     */
    public function items()
    {
        if (!isset($this->_items)) {
            $stream = fopen($this->_filename, 'r');
            if ($stream) {
                flock($stream, LOCK_SH);
                $this->_items = unserialize(stream_get_contents($stream));
                if ($this->_items === false) {
                    throw new RuntimeException("Can't read {$this->_filename}", 1);
                }
                ksort($this->_items);
                flock($stream, LOCK_UN);
                fclose($stream);
            } else {
                throw new RuntimeException("Can't read {$this->_filename}", 1);
            }
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

    /**
     * Modifies the data file while it is exclusively locked.
     *
     * @param callback $modifier A function that manipulates ::_items.
     *
     * @return void
     *
     * @throws RuntimeException
     */
    protected function modify($modifier)
    {
        $stream = fopen($this->_filename, 'r+');
        if ($stream) {
            flock($stream, LOCK_EX);
            $this->_items = unserialize(stream_get_contents($stream));
            $modifier();
            fseek($stream, 0);
            ftruncate($stream, 0);
            fwrite($stream, serialize($this->_items));
            flock($stream, LOCK_UN);
            fclose($stream);
        } else {
            throw new RuntimeException("Can't write {$this->_filename}", 2);
        }
    }

    /**
     * Sets the available amount of an item.
     *
     * @return void
     */
    protected function doSetItemAmount()
    {
        $this->_items[$this->_tempItem] = $this->_tempAmount;
    }

    /**
     * Sets the available amount of an item.
     *
     * @param string $item   An item name.
     * @param int    $amount An amount.
     *
     * @return void
     *
     * @todo Rewrite with a closure (requires PHP 5.4).
     */
    public function setItemAmount($item, $amount)
    {
        assert($amount >= 0);

        $this->_tempItem = $item;
        $this->_tempAmount = $amount;
        $this->modify(array($this, 'doSetItemAmount'));
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
     * @return void
     */
    protected function doRemoveItem()
    {
        unset($this->_items[$this->_tempItem]);
    }

    /**
     * Removes an item.
     *
     * @param string $name An item name.
     *
     * @return void
     *
     * @todo Rewrite with a closure (requires PHP 5.4).
     */
    public function removeItem($name)
    {
        assert($this->hasItem($name));

        $this->_tempItem = $name;
        $this->modify(array($this, 'doRemoveItem'));
    }

    /**
     * Reserves a certain amount of items and returns whether that succeeded.
     *
     * @param string $item   An existing item name.
     * @param int    $amount An amount.
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    public function reserve($item, $amount)
    {
        assert($this->hasItem($item));
        assert($amount > 0);

        $result = false;
        $this->_stream = fopen($this->_filename, 'r+');
        if ($this->_stream) {
            flock($this->_stream, LOCK_EX);
            $this->_items = unserialize(stream_get_contents($this->_stream));
            if ($amount <= $this->_items[$item]) {
                $this->_items[$item] -= $amount;
                $result = true;
            } else {
                flock($this->_stream, LOCK_UN);
                fclose($this->_stream);
                $this->_stream = null;
            }
        } else {
            throw new RuntimeException("Can't write {$this->_filename}", 2);
        }
        return $result;
    }

    /**
     * Returns whether a reservation is in progress.
     *
     * @return bool
     */
    public function reservationInProgress()
    {
        return isset($this->_stream);
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

    /**
     * Rolls back the current reservation.
     *
     * @return void
     */
    public function rollbackReservation()
    {
        flock($this->_stream, LOCK_UN);
        fclose($this->_stream);
        $this->_stream = null;
    }
}

?>
