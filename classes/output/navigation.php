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

namespace local_invitation\output;

use local_invitation\globals as gl;
use local_invitation\helper\util;

/**
 * A class to manipulate the moodle navigation.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation extends \plugin_renderer_base {
    /**
     * Create a new navigation node.
     *
     * @return \navigation_node|null The navigation node
     */
    public static function create_navigation_node() {
        $PAGE   = gl::page();
        $COURSE = gl::course();
        $DB     = gl::db();

        if ($COURSE->id == SITEID) {
            return null;
        }

        if (!util::is_active()) {
            return null;
        }

        $context = \context_course::instance($COURSE->id);
        // Are we really on the course page or maybe in an activity page?
        if ($PAGE->context->id !== $context->id) {
            // If the course has no sections the activity page might be the course page.
            if (course_format_uses_sections($COURSE->format)) {
                return null;
            }
        }

        if (!has_capability('local/invitation:manage', $context)) {
            return null;
        }

        if (!is_enrolled($context, null, '', true)) {
            if (!is_viewing($context)) {
                if (!is_siteadmin()) {
                    return null;
                }
            }
        }

        if ($DB->get_record('local_invitation', ['courseid' => $COURSE->id])) {
            $nodetitle = get_string('edit_invitation', 'local_invitation');
            $pixname   = 'envelope-open';
        } else {
            $nodetitle = get_string('invite_participants', 'local_invitation');
            $pixname   = 'envelope';
        }
        $newnode = \navigation_node::create(
            $nodetitle,
            new \moodle_url('/local/invitation/invite.php', ['courseid' => $COURSE->id]),
            \global_navigation::TYPE_ROOTNODE,
            null,
            null,
            new \pix_icon($pixname, $nodetitle, 'local_invitation')
        );

        return $newnode;
    }

    /**
     * Create a rendered action element for user navigation (Top navigation left from user avatar).
     *
     * @return string The html
     */
    public static function create_nav_action() {
        $OUTPUT = gl::output();

        $config = get_config('local_invitation');
        if (empty($config->showinusernavigation)) {
            return '';
        }

        if (!$navigationnode = static::create_navigation_node()) {
            return '';
        }

        $content = new \stdClass();
        $content->text = $navigationnode->text;
        $content->url = $navigationnode->action;
        $content->icon = $OUTPUT->render($navigationnode->icon);
        return $OUTPUT->render_from_template('local_invitation/navbar_action', $content);
    }
}
