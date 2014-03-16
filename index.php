<?php

$_Booking_tag = null;

$_Booking_stream = null;

function Booking_numberSuffix($number)
{
    if ($number == 1) {
        $result = 'singular';
    } elseif ($number >= 2 && $number <= 4) {
        $result = 'paucal';
    } else {
        $result = 'plural';
    }
    return $result;
}

function Booking_dataFolder()
{
    global $pth;

    return $pth['folder']['plugins'] . 'booking/data/';
}

function Booking_filename()
{
    global $_Booking_tag;

    $filename = Booking_dataFolder() . $_Booking_tag . '.txt';
    return $filename;
}

function Booking_free($keepOpen = false)
{
    global $_Booking_stream;

    $filename = Booking_filename();
    if (is_readable($filename) &&
        ($_Booking_stream = fopen($filename, 'r')) !== false)
    {
        flock($_Booking_stream, $keepOpen ? LOCK_EX : LOCK_SH);
        $contents = stream_get_contents($_Booking_stream);
        if (!$keepOpen) {
            flock($_Booking_stream, LOCK_UN);
            fclose($_Booking_stream);
            $_Booking_stream = null;
        }
        $free = (int) trim($contents);
    } else {
        $free = 0;
    }
    return $free;
}

function Booking_write($free)
{
    global $_Booking_stream;

    $result = false;
    $filename = Booking_filename();
    if (!isset($_Booking_stream)
        && ($_Booking_stream = fopen($filename, 'a+')) !== false)
    {
        flock($_Booking_stream, LOCK_EX);
        fseek($_Booking_stream, 0);
        ftruncate($_Booking_stream, 0);
    }
    if ($_Booking_stream) {
        $result = (bool) fwrite($_Booking_stream, $free);
        flock($_Booking_stream, LOCK_UN);
        fclose($_Booking_stream);
        $_Booking_stream = null;
    }
    return $result;
}

function bookingfree($tag)
{
    global $plugin_tx, $_Booking_tag;

    $_Booking_tag = $tag;
    $free = Booking_free();
    if ($free > 0) {
        $suffix = Booking_numberSuffix($free);
        $result = sprintf($plugin_tx['booking']["free_$suffix"], $free);
    } else {
        $result = $plugin_tx['booking']['booked_out'];
    }
    return $result;
}

function bookingform($formName, $tag)
{
    global $pth, $plugin_tx, $_Booking_tag;

    $ptx = $plugin_tx['booking'];
    if (!preg_match('/^[a-z0-9-]+$/', $tag)) {
        return sprintf($ptx['invalid_tag'], $tag);
    }
    $_Booking_tag = $tag;
    if (($free1 = Booking_free()) > 0) {
        include_once $pth['folder']['plugins'] . 'booking/advancedform.php';
        $o = advancedform($formName);
        $free2 = Booking_free();
        if ($free2 == $free1) {
            $o = bookingfree($tag) . $o;
        }
        return $o;
    } else {
        return $ptx['booked_out'];
    }
}