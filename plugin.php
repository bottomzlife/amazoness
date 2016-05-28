<?php
/*
    Plugin Name: Amazoness
    Plugin URI: http://netsp.in/
    Description: A WordPress plugin that provides shortcodes for Amazon Associates tags
    Version: 0.9.1
    Author: bottomzlife
    Author URI: http://netsp.in/
    License: GPL2
*/

/*  Copyright 2016 bottomzlife (email : spam@netsp.in)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

call_user_func(function () {
    $THIS_PLUGIN_PATH = dirname(__FILE__);
    set_include_path(
        get_include_path()
        . PATH_SEPARATOR
        . $THIS_PLUGIN_PATH
        . '/lib/'
    );
    require 'vendor/Netspin/Amazoness.php';
    \Netspin\Amazoness::load_gettext_textdomain();
    function gg( $message ) {
        return __( $message, \Netspin\Amazoness::PLUGIN_NAME_ASCII );
    }
    add_shortcode(
        'asin',
        array( 'Netspin\Amazoness', 'callback_shortcode' )
    );
    add_action(
        'wp_head',
        array( 'Netspin\Amazoness', 'callback_wp_head' ),
        99
    );
});
