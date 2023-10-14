<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of hook functions to manipulate the navigation or do some other stuff.
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_invitation\globals as gl;
use local_invitation\helper\util;
use local_invitation\output\navigation as nav;

/**
 * Allow plugins to provide some content to be rendered in the navbar.
 * The plugin must define a PLUGIN_render_navbar_output function that returns
 * the HTML they wish to add to the navbar.
 *
 * @return string HTML for the navbar
 */
function local_invitation_render_navbar_output() {
    return nav::create_nav_action();
}

/**
 * Fumble with Moodle's global navigation by leveraging Moodle's *_extend_navigation() hook.
 *
 * @param global_navigation $navigation
 */
function local_invitation_extend_navigation(global_navigation $navigation) {
    $USER = gl::user();

    // Prevent some urls to invited users.
    util::prevent_actions($USER);
}

/**
 * Fumble with Moodle's global navigation by leveraging Moodle's *_extend_navigation_course() hook.
 *
 * @param navigation_node $navigation
 */
function local_invitation_extend_navigation_course(navigation_node $navigation) {
    if ($newnode = nav::create_navigation_node()) {
        $navigation->add_node($newnode);
    }
}

/**
 * Get icon mapping for FontAwesome.
 */
function local_invitation_get_fontawesome_icon_map() {
    // We build a map of some icons we use in the navigation.
    $iconmap = [
        'local_invitation:envelope'      => 'fa-envelope-o',
        'local_invitation:envelope-open' => 'fa-envelope-open-o',
    ];

    return $iconmap;
}
