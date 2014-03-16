<?php

/**
 * Advancedform_XH hooks of Monorder_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Monorder
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Monorder_XH
 */

/**
 * Returns whether there are enough items available.
 *
 * @param string $form_name  A form name.
 * @param string $field_name A field name.
 * @param mixed  $value      A field value.
 *
 * @return bool
 *
 * @global array               The configuration of the plugins.
 * @global Monorder_Controller The controller object.
 */
function advfrm_custom_valid_field($form_name, $field_name, $value)
{
    global $plugin_cf, $_Monorder;

    if ($field_name == $plugin_cf['monorder']['field_name']) {
        $result = $_Monorder->reserve($value);
    } else {
        $result = true;
    }
    return $result;
}

/**
 * Books an order.
 *
 * @param string $form_name       A form name.
 * @param object &$mail           A PHPMailer object.
 * @param bool   $is_confirmation Whether the mail is the confirmation mail.
 *
 * @return void
 *
 * @global array               The configuration of the plugins.
 * @global Monorder_Controller The controller object.
 */
function advfrm_custom_mail($form_name, &$mail, $is_confirmation)
{
    global $plugin_cf, $_Monorder;

    $fieldname = 'advfrm-' . $plugin_cf['monorder']['field_name'];
    if (!$is_confirmation) {
        $_Monorder->commitReservation();
    }
}

?>
