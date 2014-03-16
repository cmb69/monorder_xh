<?php

/**
 * The controller class.
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
 * The controller class.
 *
 * @category CMSimple_XH
 * @package  Monorder
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Monorder_XH
 *
 */
class Monorder_Controller
{
    /**
     * The model.
     *
     * @var Monorder_Model
     */
    private $_model;

    /**
     * The views.
     *
     * @var Monorder_Views
     */
    private $_views;

    /**
     * The name of the current item.
     *
     * @var string
     */
    private $_currentItem;

    /**
     * Initializes a new instance.
     */
    public function __construct()
    {
        $this->_model = new Monorder_Model();
        $this->_views = new Monorder_Views($this->_model);
        $this->dispatch();
    }

    /**
     * Returns the name of the current item.
     *
     * @return string
     */
    public function currentItem()
    {
        return $this->_currentItem;
    }

    /**
     * Returns the item list view.
     *
     * @return (X)HTML.
     */
    protected function items()
    {
        return $this->_views->itemList();
    }

    /**
     * Creates a new item and returns the file list view.
     *
     * @return string (X)HTML.
     *
     * @global array  The localization of the plugins.
     */
    protected function newItem()
    {
        global $plugin_tx;

        $o = '';
        if (isset($_POST['monorder_item'])) {
            $item = stsl($_POST['monorder_item']); // TODO sanitize
            try {
                $this->_model->setItemAmount($item, 0);
                $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
            } catch (Exception $x) {
                e('cntwriteto', 'file', $this->_model->filename());
            }
        }
        $o .= $this->_views->itemList();
        return $o;
    }

    /**
     * Deletes an item and returns the file list view.
     *
     * @return string (X)HTML.
     *
     * @global array  The localization of the plugins.
     */
    protected function deleteItem()
    {
        global $plugin_tx;

        $o = '';
        if (isset($_POST['monorder_item'])) {
            $item = stsl($_POST['monorder_item']); // TODO sanitize
            try {
                $this->_model->removeItem($item);
                $o .= '<p>' . $plugin_tx['monorder']['successfully_deleted'] . '</p>';
            } catch (Exception $x) {
                e('cntdelete', 'file', $filename);
            }
        }
        $o .= $this->_views->itemList();
        return $o;
    }

    /**
     * Returns an item edit view.
     *
     * @return string (X)HTML.
     */
    protected function editItem()
    {
        $item = stsl($_GET['monorder_item']);
        return $this->_views->itemForm($item);
    }

    /**
     * Saves an event file and return the file list view.
     *
     * @return string (X)HTML.
     *
     * @global array  The localization of the plugins.
     */
    protected function saveItem()
    {
        global $plugin_tx;

        $o = '';
        $item = stsl($_GET['monorder_item']); // TODO sanitize
        if (isset($_POST['monorder_free'])) {
            $amount = stsl($_POST['monorder_free']);
            try {
                $this->_model->setItemAmount($item, $amount);
                $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
            } catch (Exception $ex) {
                e('cntwriteto', 'file', $this->_model->filename());
            }
        }
        $o .= $this->_views->itemForm($item);
        return $o;
    }

    /*
     * Handle the administration.
     *
     * @return void
     *
     * @global string Output for the contents area.
     * @global string The value of the admin GP parameter.
     * @global string The value of the action GP parameter.
     */
    protected function handleAdministration()
    {
        global $o, $admin, $action;

        $o .= print_plugin_admin('off');
        switch ($admin) {
        case '':
            switch ($action) {
            case 'plugin_text':
                $o .= $this->editItem();
                break;
            case 'plugin_textsave':
                $o .= $this->saveItem();
                break;
            case 'new_event':
                $o .= $this->newItem();
                break;
            case 'delete_event':
                $o .= $this->deleteItem();
                break;
            default:
                $o .= $this->items();
            }
            break;
        default:
            $o .= plugin_admin_common($action, $admin, 'monorder');
        }
    }

    /**
     * Dispatches.
     *
     * @return void
     *
     * @global $string Whether the plugin administration is requested.
     */
    protected function dispatch()
    {
        global $monorder;

        if (XH_ADM && isset($monorder) && $monorder == 'true') {
            $this->handleAdministration();
        }
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
        return $this->_model->reserve($item, $amount);
    }

    /**
     * Commits the current reservation.
     *
     * @return void
     */
    public function commitReservation()
    {
        $this->_model->commitReservation();
    }

    /**
     * Returns an inventory view.
     *
     * @param string $item An item name.
     *
     * @return string (X)HTML.
     */
    public function inventory($item)
    {
        return $this->_views->inventory($item);
    }

    /**
     * Returns an order form.
     *
     * @param string $formName A name of an Advancedform_XH form.
     * @param string $itemName An item name.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the plugins.
     */
    public function orderForm($formName, $itemName)
    {
        global $pth, $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        if (!isset($this->_currentItem)) {
            $this->_currentItem = $itemName;
        } else {
            return '<p>ERROROROROOR</p>';
        }
        if (($free1 = $this->_model->availableAmountOf($itemName)) > 0) {
            include_once $pth['folder']['plugins'] . 'monorder/advancedform.php';
            $o = advancedform($formName);
            $this->_model->clearCache();
            $free2 = $this->_model->availableAmountOf($itemName);
            if ($free2 == $free1) {
                $o = $this->_views->inventory($itemName) . $o;
            }
            return $o;
        } else {
            return $ptx['booked_out'];
        }
    }
}

?>
