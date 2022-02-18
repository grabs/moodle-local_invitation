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
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_invitation\helper\date_time as datetime;
use local_invitation\helper\util as util;
use local_invitation\globals as gl;
use local_invitation\output\navigation as nav;

/**
 * Fumble with Moodle's global navigation by leveraging Moodle's *_extend_navigation() hook.
 *
 * @param global_navigation $navigation
 */
function local_invitation_extend_navigation(global_navigation $navigation) {
    $COURSE = gl::course();
    $USER = gl::user();

    // Prevent some urls to invited users.
    util::prevent_actions($USER);

    if ($newnode = nav::create_navigation_node()) {
        $newnode->showdivider = true;
        $newnode->collectionlabel = $newnode->text;

        $myhomenode = $navigation->find($COURSE->id, global_navigation::TYPE_COURSE);
        foreach ($myhomenode->children as $c) {
            $c->showdivider = true;
            $c->collectionlabel = $c->shorttext;
            $myhomenode->add_node($newnode, $c->key);
            return;
        }
    }
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
    $iconmap = array(
        'local_invitation:envelope' => 'fa-envelope-o',
        'local_invitation:envelope-open' => 'fa-envelope-open-o',
    );

    return $iconmap;
}
