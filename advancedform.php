<?php

/**
 * Prueft ob noch genuegend Plaetze frei sind.
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
 * Verbucht die Platzreservierung.
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
