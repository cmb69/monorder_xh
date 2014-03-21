<?php

/**
 * The views class.
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
 * The views class.
 *
 * @category CMSimple_XH
 * @package  Monorder
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Monorder_XH
 */
class Monorder_Views
{
    /**
     * The model.
     *
     * @var Monorder_Model
     */
    private $_model;

    /**
     * Initializes a new instance.
     *
     * @param Monorder_Model $model A model.
     */
    public function __construct(Monorder_Model $model)
    {
        $this->_model = $model;
    }

    /**
     * Returns a string with all special HTML characters encoded as entities.
     *
     * @param string $string A string.
     *
     * @return string
     */
    protected function hsc($string)
    {
        if (function_exists('XH_hsc')) {
            return XH_hsc($string);
        } else {
            return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
        }
    }

    /**
     * Returns a string with ETAGCs adjusted to the configured markup language.
     *
     * @param string $string A string.
     *
     * @return string (X)HTML.
     *
     * @global array The configuration of the core.
     */
    protected function xhtml($string)
    {
        global $cf;

        if (!$cf['xhtml']['endtags']) {
            $string = str_replace(' />', '>', $string);
        }
        return $string;
    }

    /**
     * Returns a message.
     *
     * @param string $type    A message type ('success', 'info', 'warning', 'fail').
     * @param string $message A message.
     *
     * @return string (X)HTML.
     */
    public function message($type, $message)
    {
        if (function_exists('XH_message')) {
            return XH_message($type, $message);
        } else {
            $class = in_array($type, array('warning', 'fail'))
                ? 'cmsimplecore_warning'
                : '';
            return '<p class="' . $class . '">' . $message . '</p>';
        }
    }

    /**
     * Returns a single system check list item.
     *
     * @param string $check A label.
     * @param string $state A state.
     *
     * @return string XHTML.
     *
     * @global array The paths of system files and folders.
     */
    protected function systemCheckItem($check, $state)
    {
        global $pth;

        $imageFolder = $pth['folder']['plugins'] . 'monorder/images/';
        return <<<EOT
<li><img src="$imageFolder$state.png" alt="$state" /> $check</li>
EOT;
    }

    /**
     * Returns an administration heading.
     *
     * @param string $title A title.
     *
     * @return string (X)HTML.
     */
    public function administrationHeading($title)
    {
        $title = $this->hsc($title);
        return <<<EOS
<h1>Monorder &ndash; $title</h1>
EOS;
    }

    /**
     * Returns the synopsis view.
     *
     * @return string (X)HTML.
     */
    public function synopsis()
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $formName = $ptx['synopsis_form_name'];
        $itemName = $ptx['synopsis_item_name'];
        $result = <<<EOT
<h4>$ptx[synopsis_title]</h4>
<pre>{{{PLUGIN:monorder_form('$formName', '$itemName');}}}</pre>
<pre>{{{PLUGIN:monorder_inventory('$itemName');}}}</pre>
EOT;
        return $this->xhtml($result);
    }

    /**
     * Returns the system check view.
     *
     * @param array $checks An associative array of system checks (label => state).
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    public function systemCheck($checks)
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $items = array();
        foreach ($checks as $check => $state) {
            $items[] = $this->systemCheckItem($check, $state);
        }
        $items = implode('', $items);
        $o = <<<EOT
<h4>$ptx[syscheck_title]</h4>
<ul class="pdeditor_system_check">
    $items
</ul>
EOT;
        return $this->xhtml($o);
    }

    /**
     * Returns summarized GPLv3 license information.
     *
     * @return string XHTML.
     */
    protected function license()
    {
        return <<<EOT
<p class="monorder_license">
    This program is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as published by the Free
    Software Foundation, either version 3 of the License, or (at your option)
    any later version.
</p>
<p class="monorder_license">
    This program is distributed in the hope that it will be useful, but
    <em>without any warranty</em>; without even the implied warranty of
    <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
    the GNU General Public License for more details.
</p>
<p class="monorder_license">
    You should have received a copy of the GNU General Public License along with
    this program. If not, see <a href="http://www.gnu.org/licenses/">
    http://www.gnu.org/licenses/</a>.
</p>
EOT;
    }

    /**
     * Returns the plugin about information view.
     *
     * @return string (X)HTML.
     */
    public function about()
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $logoPath = $this->_model->logoPath();
        $version = MONORDER_VERSION;
        $license = $this->license();
        $o = <<<EOT
<h4>$ptx[info_about]</h4>
<img src="$logoPath" width="128" height="128" alt="$ptx[alt_logo]"
        style="float: left; margin-right: 16px" />
<p>Version: $version</p>
<p>Copyright &copy; 2014 <a href="http://3-magi.net/">Christoph M. Becker</a></p>
$license
EOT;
        return $this->xhtml($o);
    }

    /**
     * Returns a single list item of the item list view.
     *
     * @param string $item       An item name.
     * @param string $scriptName A script name.
     *
     * @return string (X)HTML.
     *
     * @global array  The localization of the plugins.
     */
    protected function itemListItem($item, $scriptName)
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $href = $this->hsc(
            $scriptName . '?monorder&admin=plugin_main&action=edit_item'
            . '&monorder_item=' . urlencode($item)
        );
        $action = $this->hsc(
            $scriptName . '?monorder&admin=plugin_main&action=delete_event'
        );
        $message = addcslashes($ptx['message_delete'], "\r\n'");
        $onsubmit = $this->hsc("return window.confirm('$message')");
        $item = $this->hsc($item);
        return <<<EOT
<li>
    <form class="monorder_delete" action="$action" method="post"
            onsubmit="$onsubmit">
        <input type="hidden" name="monorder_item" value="$item" />
        <button>$ptx[button_delete]</button>
    </form>
    <a href="$href">$item</a>
</li>
EOT;
    }

    /**
     * Returns the item list view.
     *
     * @param string $scriptName A script name.
     *
     * @return (X)HTML.
     *
     * @global string The script name.
     * @global array  The localization of the plugins.
     */
    public function itemList($scriptName)
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $action = $scriptName . '?monorder';
        $items = $this->_model->items();
        $listItems = array();
        foreach ($items as $item => $amount) {
            $listItems[] = $this->itemListItem($item, $scriptName);
        }
        $listItems = implode('', $listItems);
        $o = <<<EOT
<ul>
    $listItems
    <li>
        <form action="$action" method="post">
            <input type="hidden" name="admin" value="plugin_main" />
            <input type="hidden" name="action" value="new_event" />
            <button>$ptx[button_create]</button>
            <input type="text" name="monorder_item" />
        </form>
    </li>
</ul>
EOT;
        return $this->xhtml($o);
    }

    /**
     * Returns an item edit form.
     *
     * @param string $item       An item name.
     * @param string $scriptName A script name.
     *
     * @return string (X)HTML.
     *
     * @global array  The localization of the plugins.
     */
    public function itemForm($item, $scriptName)
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $action = $this->hsc(
            $scriptName . '?monorder&admin=plugin_main&action=save_item'
            . '&monorder_item=' . urlencode($item)
        );
        $amount = $this->_model->availableAmountOf($item);
        $item = $this->hsc($item);
        $o = <<<EOT
<form action="$action" method="post">
    <p>
        <label>
            $ptx[label_available]
            <input type="text" name="monorder_amount" value="$amount" />
        </label>
    </p>
    <button>$ptx[button_save]</button>
</form>
EOT;
        return $this->xhtml($o);
    }

    /**
     * Returns an inventory view.
     *
     * @param string $item An item name.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    public function inventory($item)
    {
        global $plugin_tx;

        $amount = $this->_model->availableAmountOf($item);
        if ($amount > 0) {
            $suffix = $this->_model->number($amount);
            $result = sprintf($plugin_tx['monorder']["avail_$suffix"], $amount);
        } else {
            $result = $plugin_tx['monorder']['avail_zero'];
        }
        return $result;
    }

}

?>
