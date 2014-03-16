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
 * @global array  The configuration of the plugins.
 * @global array  The localization of the plugins.
 * @global string The current monorder tag.
 */
function advfrm_custom_valid_field($form_name, $field_name, $value)
{
    global $plugin_cf, $plugin_tx, $_Monorder_tag;

    if ($field_name == $plugin_cf['monorder']['field_name']) {
        $free = Monorder_free();
        if ($free >= $value) {
            return true;
        } else {
            return $plugin_tx['monorder']['overbooked'];
        }
    } else {
        return true;
    }
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
 * @global array The configuration of the plugins.
 */
function advfrm_custom_mail($form_name, &$mail, $is_confirmation)
{
    global $plugin_cf;

    $fieldname = 'advfrm-' . $plugin_cf['monorder']['field_name'];

    if (!$is_confirmation) {
        $free = Monorder_free();
        $current = (int) stsl($_POST[$fieldname]);
        $free -= $current;
        Monorder_write($free);
    }
}

?>
