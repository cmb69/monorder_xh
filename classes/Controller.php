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
     * Returns the system checks.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the core.
     * @global array The localization of the plugins.
     */
    protected function systemChecks()
    {
        global $pth, $tx, $plugin_tx;

        $phpVersion = '5.3.0';
        $ptx = $plugin_tx['monorder'];
        $checks = array();
        $checks[sprintf($ptx['syscheck_phpversion'], $phpVersion)]
            = version_compare(PHP_VERSION, $phpVersion) >= 0 ? 'ok' : 'fail';
        foreach (array() as $extension) {
            $checks[sprintf($ptx['syscheck_extension'], $extension)]
                = extension_loaded($extension) ? 'ok' : 'fail';
        }
        $checks[$ptx['syscheck_magic_quotes']]
            = !get_magic_quotes_runtime() ? 'ok' : 'fail';
        $checks[$ptx['syscheck_encoding']]
            = strtoupper($tx['meta']['codepage']) == 'UTF-8' ? 'ok' : 'warn';
        $folders = array();
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $pth['folder']['plugins'] . 'monorder/' . $folder;
        }
        $folders[] = dirname($this->_model->filename()) . '/';
        foreach ($folders as $folder) {
            $checks[sprintf($ptx['syscheck_writable'], $folder)]
                = is_writable($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }

    /**
     * Return the plugin information.
     *
     * @return string (X)HTML.
     *
     * @todo Add synopsis.
     */
    public function info()
    {
        return '<h1>Monorder &ndash; Info</h1>'
            . $this->_views->systemCheck($this->systemChecks())
            . $this->_views->about();
    }

    /**
     * Returns the item list view.
     *
     * @return (X)HTML.
     *
     * @global string The script name.
     */
    protected function items()
    {
        global $sn, $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        return $this->_views->administrationHeading($ptx['events'])
            . $this->_views->itemList($sn);
    }

    /**
     * Creates a new item and returns the file list view.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    protected function newItem()
    {
        global $sn, $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $o = $this->_views->administrationHeading($ptx['events']);
        if (isset($_POST['monorder_item'])) {
            $item = stsl($_POST['monorder_item']); // TODO sanitize
            try {
                $this->_model->setItemAmount($item, 0);
                $o = $this->_views->administrationHeading($item);
                $o .= $this->_views->message(
                    'success', sprintf($ptx['message_saved'], $item)
                );
                $o .= $this->_views->itemForm($item, $sn);
            } catch (Exception $ex) {
                $o = $this->_views->administrationHeading($ptx['events']);
                $o .= $this->_views->message(
                    'fail',
                    sprintf($ptx['message_cant_write'], $this->_model->filename())
                );
                $o .= $this->_views->itemList($sn);
            }
        } else {
            $o = $this->_views->administrationHeading($ptx['events']);
            $o .= $this->_views->itemList($sn);
        }
        return $o;
    }

    /**
     * Deletes an item and returns the file list view.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    protected function deleteItem()
    {
        global $sn, $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $o = $this->_views->administrationHeading($ptx['events']);
        if (isset($_POST['monorder_item'])) {
            $item = stsl($_POST['monorder_item']); // TODO sanitize
            try {
                $this->_model->removeItem($item);
                $o .= $this->_views->message(
                    'success', sprintf($ptx['message_deleted'], $item)
                );
            } catch (Exception $x) {
                $o .= $this->_views->message(
                    'fail',
                    sprintf($ptx['message_cant_write'], $this->_model->filename())
                );
            }
        }
        $o .= $this->_views->itemList($sn);
        return $o;
    }

    /**
     * Returns an item edit view.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     */
    protected function editItem()
    {
        global $sn;

        $item = stsl($_GET['monorder_item']);
        return $this->_views->itemForm($item, $sn);
    }

    /**
     * Saves an event file and return the file list view.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    protected function saveItem()
    {
        global $sn, $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $o = $this->_views->administrationHeading($ptx['events']);
        $item = stsl($_GET['monorder_item']); // TODO sanitize
        if (isset($_POST['monorder_free'])) {
            $amount = stsl($_POST['monorder_free']);
            try {
                $this->_model->setItemAmount($item, $amount);
                $o .= $this->_views->message(
                    'success', sprintf($ptx['message_saved'], $item)
                );
            } catch (Exception $ex) {
                $o .= $this->_views->message(
                    'fail',
                    sprintf($ptx['message_cant_write'], $this->_model->filename())
                );
            }
        }
        $o .= $this->_views->itemList($sn);
        return $o;
    }

    /**
     * Handles the administration.
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

        $o .= print_plugin_admin('on');
        switch ($admin) {
        case '':
            $o .= $this->info();
            break;
        case 'plugin_main':
            switch ($action) {
            case 'edit_item':
                $o .= $this->editItem();
                break;
            case 'save_item':
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
     * Reserves a certain amount of items and returns the result.
     *
     * If the reservation succeeded <var>true</var> is returned, otherwise an
     * error message.
     *
     * @param int $amount An amount.
     *
     * @return mixed
     */
    public function reserve($amount)
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        try {
            if ($this->_model->reserve($this->_currentItem, $amount)) {
                $result = true;
            } else {
                $result = $ptx['overbooked'];
            }
        } catch (RuntimeException $ex) {
            if (XH_ADM) {
                $name = $this->_model->filename();
            } else {
                $name = $this->_currentItem;
            }
            $result = sprintf($ptx['message_cant_write'], $name);
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
