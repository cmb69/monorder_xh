<?php

/**
 * The controller class.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Monorder
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2019 Christoph M. Becker <http://3-magi.net/>
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
     * Whether the sent form has an amount field. <var>null</var> means that the
     * form has not been sent, so this field is irrelevant.
     *
     * @var bool
     */
    public $hadAmountField;

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

        $phpVersion = '5.1.0';
        $ptx = $plugin_tx['monorder'];
        $checks = array();
        $checks[sprintf($ptx['syscheck_phpversion'], $phpVersion)]
            = version_compare(PHP_VERSION, $phpVersion) >= 0 ? 'ok' : 'fail';
        foreach (array('pcre') as $extension) {
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
     */
    protected function info()
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        return $this->_views->administrationHeading($ptx['info_title'])
            . $this->_views->synopsis()
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
        return $this->_views->administrationHeading($ptx['label_items'])
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
        if (isset($_POST['monorder_item'])) {
            $this->_checkHasValidCsrfToken();
            $item = trim(stsl($_POST['monorder_item']));
            if ($item != '' && !$this->_model->hasItem($item)) {
                $this->_model->addItem($item);
                $o = $this->_views->administrationHeading($item);
                $o .= $this->_views->message(
                    'success', sprintf($ptx['message_saved'], $item)
                );
                $o .= $this->_views->itemForm($item, $sn);
                return $o;
            } else {
                return $this->items($sn);
            }
        } else {
            return $this->items($sn);
        }
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
        $o = $this->_views->administrationHeading($ptx['label_items']);
        if (isset($_POST['monorder_item'])) {
            $this->_checkHasValidCsrfToken();
            $item = stsl($_POST['monorder_item']);
            if ($this->_model->hasItem($item)) {
                $this->_model->removeItem($item);
                $o .= $this->_views->message(
                    'success', sprintf($ptx['message_deleted'], $item)
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
        return $this->_views->administrationHeading($item)
            . $this->_views->itemForm($item, $sn);
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
        $o = $this->_views->administrationHeading($ptx['label_items']);
        if (isset($_GET['monorder_item']) && isset($_POST['monorder_amount'])) {
            $this->_checkHasValidCsrfToken();
            $item = stsl($_GET['monorder_item']);
            $amount = trim(stsl($_POST['monorder_amount']));
            if ($this->_model->hasItem($item)
                && preg_match('/^[0-9]+$/', $amount)
            ) {
                $this->_model->setItemAmount($item, $amount);
                $o .= $this->_views->message(
                    'success', sprintf($ptx['message_saved'], $item)
                );
            }
        }
        $o .= $this->_views->itemList($sn);
        return $o;
    }

    /**
     * Checks whether the CSRF token is valid, if CSRF protection is available.
     *
     * @return void
     *
     * @global XH_CSRFProtection The CSRF protector.
     */
    private function _checkHasValidCsrfToken()
    {
        global $_XH_csrfProtection;

        if (isset($_XH_csrfProtection)) {
            $_XH_csrfProtection->check();
        }
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
        try {
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
        } catch (Monorder_Exception $ex) {
            $o .= $this->_views->message('fail', $ex->getMessage());
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

        if (XH_ADM 
            && (function_exists('XH_wantsPluginAdministration') && XH_wantsPluginAdministration('monorder')
            || isset($monorder) && $monorder == 'true')
        ) {
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
        if ($this->_model->reserve($this->_currentItem, $amount)) {
            $result = true;
        } else {
            $result = $ptx['avail_not_enough'];
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
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        try {
            $item = html_entity_decode($item, ENT_COMPAT, 'UTF-8');
            if ($this->_model->hasItem($item)) {
                return $this->_views->inventory($item);
            } else {
                return $this->_views->message(
                    'fail', sprintf($ptx['message_unknown_item'], $item)
                );
            }
        } catch (Monorder_Exception $ex) {
            return $this->_views->message('fail', $ex->getMessage());
        }
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
     * @global array The configuration of the plugins.
     * @global array The localization of the plugins.
     */
    public function orderForm($formName, $itemName)
    {
        global $pth, $plugin_cf, $plugin_tx;

        try {
            $pcf = $plugin_cf['monorder'];
            $ptx = $plugin_tx['monorder'];
            $itemName = html_entity_decode($itemName, ENT_COMPAT, 'UTF-8');
            if (!isset($this->_currentItem)) {
                $this->_currentItem = $itemName;
            } else {
                return $this->_views->message('fail', $ptx['message_once_only']);
            }
            if (!$this->_model->hasItem($itemName)) {
                return $this->_views->message(
                    'fail', sprintf($ptx['message_unknown_item'], $itemName)
                );
            }
            if (($amountBefore = $this->_model->availableAmountOf($itemName)) > 0) {
                include_once $pth['folder']['plugins'] . 'monorder/advancedform.php';
                $this->hadAmountField = null;
                $output = advancedform($formName);
                if ($this->hadAmountField !== false) {
                    $o = $output;
                } else {
                    $message = str_replace(
                        array('%FORM_NAME%', '%FIELD_NAME%'),
                        array($formName, $pcf['advancedform_amount_field']),
                        $ptx['message_amount_field_missing']
                    );
                    $o = $this->_views->message('fail', $message);
                }
                if ($this->_model->reservationInProgress()) {
                    $this->_model->rollbackReservation();
                }
                $this->_model->clearCache();
                $amountAfter = $this->_model->availableAmountOf($itemName);
                if ($amountAfter == $amountBefore) {
                    $o = $this->_views->inventory($itemName) . $o;
                }
                return $o;
            } else {
                return $ptx['avail_zero'];
            }
        } catch (Monorder_Exception $ex) {
            return $this->_views->message('fail', $ex->getMessage());
        }
    }
}

?>
