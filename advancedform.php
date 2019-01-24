<?php

/**
 * Advancedform_XH hooks of Monorder_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Monorder
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2019 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Monorder_XH
 */

/**
 * Returns the default value for the <var>advancedform_item_field</var>.
 *
 * @param string $formName  A form name.
 * @param string $fieldName A field name.
 * @param string $opt       A radiobutton or checkbox value.
 * @param string $isResent  Whether the form is resent.
 *
 * @return mixed
 *
 * @global array               The configuration of the plugins.
 * @global Monorder_Controller The monorder controller object.
 */
function advfrm_custom_field_default($formName, $fieldName, $opt, $isResent)
{
    global $plugin_cf, $_Monorder;

    if ($fieldName == $plugin_cf['monorder']['advancedform_item_field']) {
        return $_Monorder->currentItem();
    } else {
        return null;
    }
}

/**
 * Returns whether the value of the special fields are valid, an error
 * message otherwise. Starts the reservation transaction.
 *
 * @param string $formName  A form name.
 * @param string $fieldName A field name.
 * @param mixed  $value     A field value.
 *
 * @return mixed
 *
 * @global array               The configuration of the plugins.
 * @global array               The localization of the plugins.
 * @global Monorder_Controller The monorder controller object.
 */
function advfrm_custom_valid_field($formName, $fieldName, $value)
{
    global $plugin_cf, $plugin_tx, $_Monorder;

    switch ($fieldName) {
    case $plugin_cf['monorder']['advancedform_item_field']:
        if ($value == $_Monorder->currentItem()) {
            return true;
        } else {
            return $plugin_tx['monorder']['message_wrong_item'];
        }
    case $plugin_cf['monorder']['advancedform_amount_field']:
        if (preg_match('/^[0-9]+$/', $value)) {
            $_Monorder->hadAmountField = true;
            return $_Monorder->reserve($value);
        } else {
            // let Advancedform_XH report the appropriate error
            return true;
        }
    default:
        return true;
    }
}

/**
 * Commits the reservation transaction.
 *
 * @param string $formName       A form name.
 * @param object &$mail          A PHPMailer object.
 * @param bool   $isConfirmation Whether the mail is the confirmation mail.
 *
 * @return void
 *
 * @global array               The configuration of the plugins.
 * @global Monorder_Controller The monorder controller object.
 */
function advfrm_custom_mail($formName, &$mail, $isConfirmation)
{
    global $plugin_cf, $_Monorder;

    if (!$_Monorder->hadAmountField) {
        $_Monorder->hadAmountField = false;
        return false;
    }
    if (!$isConfirmation) {
        $_Monorder->commitReservation();
    }
}

?>
