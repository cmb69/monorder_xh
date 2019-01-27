# Monorder\_XH 

Monorder\_XH facilitates placing order or reservation forms for single
items resp. events (hence the name which is a synaeresis of "mono" and
"order") on your website. Ordering resp. booking takes in account the
number of items on stock resp. the free places, so overbooking is not
possible.

The forms have to be created with
[Advancedform\_XH](https://github.com/cmb69/advancedform_xh), and
it is possible to use a single form for multiple sales items resp.
events. However, it is only possible to offer one form for a particular
item resp. event on each page, so if you have more demanding needs use a
shopping resp. a booking system.

Besides its actual use, Monorder\_XH serves to demonstrate the
possibilities and limitations of using Advancedform\_XH's hook system
for custom plugins.

  - [Requirements](#requirements)
  - [Download](#download)
  - [Installation](#installation)
  - [Settings](#settings)
  - [Usage](#usage)
      - [Form preparation](#form-preparation)
      - [Item preparation](#item-preparation)
      - [Displaying the form](#displaying-the-form)
      - [Inventory](#inventory)
  - [Troubleshooting](#troubleshooting)
  - [License](#license)
  - [Credits](#credits)

## Requirements

Monorder\_XH is a plugin for CMSimple\_XH ≥ 1.5.4,
PHP ≥ 5.1 and Advancedform\_XH.

## Download

The [lastest release](https://github.com/cmb69/monorder_xh/releases/latest) is available for download on Github.

## Installation

The installation is done as with many other CMSimple\_XH plugins. See
the [CMSimple\_XH
wiki](https://wiki.cmsimple-xh.org/doku.php/installation) for further
details.

1.  Backup the data on your server.
2.  Unzip the distribution on your computer.
3.  Upload the whole directory monorder/ to your server into
    CMSimple\_XH's plugins/ directory.
4.  Set write permissions to the subdirectories css/, config/,
    languages/ and the plugin's data folder.
5.  Protect Monorder\_XH's data folder against direct access by any
    means your webserver provides. .htaccess files for Apache servers
    are already placed in the default data folder.
6.  Browse to Monorder\_XH's administration to check if all requirements
    are fulfilled.

## Settings

The plugin's configuration is done as with many other CMSimple\_XH
plugins in the website's back-end. Select Plugins → Monorder.

You can change the default settings of Monorder\_XH under "Config".
Hints for the options will be displayed when hovering over the help icon
with your mouse.

Localization is done under "Language". You can translate the character
strings to your own language, or customize them according to your needs.

The look of the Monorder\_XH can be customized under "Stylesheet".

## Usage

As already mentioned in the introduction Monorder\_XH can be used for
order forms as well as reservation forms—with regard to the plugin these
concepts are identical, so in the following the term *orders* refers to
reservations as well, the term *item* refers to order or sales items as
well as events, and the term *amount* refers to the order amount as well
as the attendance of an event. For reservation purposes you should
change the respective terms in the language settings of the plugin.

### Form preparation

At first you have to prepare the order form in the mail form
administration of Advancedform\_XH. The characteristic of forms usable with
Monorder\_XH is that they *must* contain a numeric or hidden field with
the name "order\_amount", and that they *can* contain a text or hidden
field with the name "order\_item" (both names can be changed in the
configuration of the plugin, but must be the same for all forms).

Note, that hidden fields will not be contained in the confirmation mail,
and that a confirmation will only be sent, if there is a thanks page
defined for the form and it contains a field of type "Sender (e-mail)".

Further note, that while you can use Advancedform\_XH's template
system for Monorder\_XH forms, you cannot use the hook
system, as this is already used by Monorder\_XH behind the scenes.

If you want to keep a better overview of all orders, you can activate
the "store data" option of the form. Note, that all orders made via this
form will be stored in the same CSV file independent of the item. To
sort that out you can temporarily import the CSV file to a spreadsheet
software and use its capabilities.

### Item preparation

For each item you want to offer you have to create a new record in the
administration of Monorder\_XH and you have to specify its available
amount. Of course, you can adjust this amount anytime as appropriate.
The name of the item is only for internal use, unless you have placed an
order\_item field on your form, in which case its value will be the name
of the item. So it is sensible to use a speaking name, such as "Yellow
CMSimple\_XH T-shirt XL" or "CMSimple\_XH seminar, 2014-03-21".

### Displaying the form

To display an order form on a page use the following plugin call:

    {{{PLUGIN:Monorder_form('FORM_NAME', 'ITEM_NAME');}}}

where FORM\_NAME is the name of the Advancedform\_XH form, and
ITEM\_NAME is the name of the Monorder\_XH item,
    e.g.:

    {{{PLUGIN:Monorder_form('order', 'Yellow CMSimple_XH T-shirt XL');}}}

If there are items on stock the number is displayed above the form;
otherwise there will be a message that there are no items available, and
the form is not displayed at all.

When a customer submits the form, the form validation will make sure
that the number of order\_amount items is on stock, and that the value
of order\_item has not been changed to something else; otherwise the
email will not be sent. If everything is okay the email will be sent and
the order\_amount is subtracted from the stock.

### Inventory

If you like you can display the number of available items on other pages
(for instance, on an overview page). Therefore use the following plugin
call:

    {{{PLUGIN:Monorder_inventory('ITEM_NAME');}}}

Example:

    {{{PLUGIN:Monorder_inventory('Yellow CMSimple_XH T-shirt XL');}}}

## Troubleshooting

Report bugs and ask for support either on [Github](https://github.com/cmb69/monorder_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Monorder\_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Monorder\_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Monorder\_XH. If not, see <http://www.gnu.org/licenses/>.

Copyright 2014-2019 Christoph M. Becker

## Credits

The plugin was inspired by Simmyne.

The plugin icon is designed by
[Matt](http://www.freeiconsdownload.com/). Many thanks for publishing
this icon under a liberal license.

This plugin uses free applications icons from
[Aha-Soft](http://www.aha-soft.com/). Many thanks for making these icons
freely available.

Many thanks to the community at the [CMSimple\_XH
forum](http://www.cmsimpleforum.com/) for tips, suggestions and testing.

And last but not least many thanks to [Peter Harteg](http://harteg.dk/),
the "father" of CMSimple, and all developers of
[CMSimple\_XH](http://www.cmsimple-xh.org/) without whom this amazing
CMS wouldn't exist.
