<?php

$_Monorder_tag = null;

$_Monorder_stream = null;

function Monorder_numberSuffix($number)
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

function Monorder_dataFolder()
{
    global $pth;

    return $pth['folder']['plugins'] . 'monorder/data/';
}

function Monorder_filename()
{
    global $_Monorder_tag;

    $filename = Monorder_dataFolder() . $_Monorder_tag . '.txt';
    return $filename;
}

function Monorder_free($keepOpen = false)
{
    global $_Monorder_stream;

    $filename = Monorder_filename();
    if (is_readable($filename) &&
        ($_Monorder_stream = fopen($filename, 'r')) !== false)
    {
        flock($_Monorder_stream, $keepOpen ? LOCK_EX : LOCK_SH);
        $contents = stream_get_contents($_Monorder_stream);
        if (!$keepOpen) {
            flock($_Monorder_stream, LOCK_UN);
            fclose($_Monorder_stream);
            $_Monorder_stream = null;
        }
        $free = (int) trim($contents);
    } else {
        $free = 0;
    }
    return $free;
}

function Monorder_write($free)
{
    global $_Monorder_stream;

    $result = false;
    $filename = Monorder_filename();
    if (!isset($_Monorder_stream)
        && ($_Monorder_stream = fopen($filename, 'a+')) !== false)
    {
        flock($_Monorder_stream, LOCK_EX);
        fseek($_Monorder_stream, 0);
        ftruncate($_Monorder_stream, 0);
    }
    if ($_Monorder_stream) {
        $result = (bool) fwrite($_Monorder_stream, $free);
        flock($_Monorder_stream, LOCK_UN);
        fclose($_Monorder_stream);
        $_Monorder_stream = null;
    }
    return $result;
}

function monorderfree($tag)
{
    global $plugin_tx, $_Monorder_tag;

    $_Monorder_tag = $tag;
    $free = Monorder_free();
    if ($free > 0) {
        $suffix = Monorder_numberSuffix($free);
        $result = sprintf($plugin_tx['monorder']["free_$suffix"], $free);
    } else {
        $result = $plugin_tx['monorder']['booked_out'];
    }
    return $result;
}

function monorderform($formName, $tag)
{
    global $pth, $plugin_tx, $_Monorder_tag;

    $ptx = $plugin_tx['monorder'];
    if (!preg_match('/^[a-z0-9-]+$/', $tag)) {
        return sprintf($ptx['invalid_tag'], $tag);
    }
    $_Monorder_tag = $tag;
    if (($free1 = Monorder_free()) > 0) {
        include_once $pth['folder']['plugins'] . 'monorder/advancedform.php';
        $o = advancedform($formName);
        $free2 = monorder_free();
        if ($free2 == $free1) {
            $o = monorderfree($tag) . $o;
        }
        return $o;
    } else {
        return $ptx['booked_out'];
    }
}