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
        $href = $scriptName . '?monorder&amp;admin=&amp;action=plugin_text'
            . '&amp;monorder_item=' . $item;
        $action = $scriptName . '?monorder&amp;admin=&amp;action=delete_event';
        $onsubmit = 'return window.confirm(\'' . $ptx['confirm_delete'] . '\')';
        return <<<EOT
<li>
    <a href="$href">$item</a>
    <form class="monorder_delete" action="$action" method="post"
            onsubmit="$onsubmit">
        <input type="hidden" name="monorder_item" value="$item">
        <button>$ptx[label_delete]</button>
    </form>
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
        return <<<EOT
<h4>$ptx[events]</h4>
<ul>
$listItems
<li><form action="$action" method="post">
<input type="hidden" name="admin" value="">
<input type="hidden" name="action" value="new_event">
<input type="text" name="monorder_item">
<button>$ptx[new_event]</button>
</form></li>
</ul>
EOT;
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
     * @global string The current monorder tag.
     */
    public function itemForm($item, $scriptName)
    {
        global $plugin_tx;

        $ptx = $plugin_tx['monorder'];
        $action = $scriptName . '?monorder&admin=&action=plugin_textsave'
            . '&amp;monorder_item=' . $item;
        $free = $this->_model->availableAmountOf($item);
        return <<<EOT
<h4>$item</h4>
<form action="$action" method="post">
    <p>$ptx[free]</p>
    <input type="text" name="monorder_free" value="$free">
    <button>$ptx[save]</button>
</form>
EOT;
    }

    /**
     * Returns an inventory view.
     *
     * @param string $item An item name.
     *
     * @return string (X)HTML.
     *
     * @global array  The localization of the plugins.
     */
    public function inventory($item)
    {
        global $plugin_tx;

        $free = $this->_model->availableAmountOf($item);
        if ($free > 0) {
            $suffix = $this->_model->number($free);
            $result = sprintf($plugin_tx['monorder']["free_$suffix"], $free);
        } else {
            $result = $plugin_tx['monorder']['booked_out'];
        }
        return $result;
    }

}

?>
