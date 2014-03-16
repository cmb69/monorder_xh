<?php

/**
 * Prueft ob noch genuegend Plaetze frei sind.
 */
function advfrm_custom_valid_field($form_name, $field_name, $value)
{
    global $plugin_cf, $plugin_tx, $_Booking_tag;

    if ($field_name == $plugin_cf['booking']['field_name']) {
        $free = Booking_free();
        if ($free >= $value) {
            return true;
        } else {
            return $plugin_tx['booking']['overbooked'];
        }
    } else {
        return true;
    }
}

/**
 * Verbucht die Platzreservierung.
 */
function advfrm_custom_mail($form_name, &$mail, $is_confirmation)
{
    global $plugin_cf;

    $fieldname = 'advfrm-' . $plugin_cf['booking']['field_name'];

    if (!$is_confirmation) {
        $free = Booking_free();
        $current = (int) stsl($_POST[$fieldname]);
        $free -= $current;
        Booking_write($free);
    }
}
