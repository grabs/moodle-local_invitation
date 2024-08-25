<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_invitation\external;

require_once("$CFG->libdir/externallib.php");

/**
 * Provides the local_invitation_search_groups external function.
 *
 * @package     local_invitation
 * @category    external
 * @copyright   2024 Andreas Grabs
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_groups extends \external_api {

    /**
     * Describes the external function parameters.
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters([
            'courseid' => new \external_value(PARAM_INT, 'The course id where the group is searched in', VALUE_REQUIRED),
            'query' => new \external_value(PARAM_RAW, 'The search query', VALUE_REQUIRED),
        ]);
    }

    /**
     * Finds groups with the name matching the given query.
     *
     * @param int    $courseid The courseid the groups are searched in.
     * @param string $query The search request.
     * @return array
     */
    public static function execute(int $courseid, string $query): array {
        global $DB;

        $params = \external_api::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'query' => $query,
        ]);
        $query = clean_param($params['query'], PARAM_TEXT);
        // We have to trim the query because a group must not have a leading or trailing space.
        $query = trim($query);
        if (empty($query)) {
            return [
                'list' => [],
            ];
        }

        // Validate context.
        $context = \context_course::instance($courseid);
        self::validate_context($context);
        require_capability('moodle/course:managegroups', $context);

        // We want to know whether the user is typing the exact name of a group or not.
        // If the user has typed the exact name, we do not need to add an element for creating a new group.
        // If the user has only typed part of a name, we need to add an element for creating a new group.
        $courseselectsql = 'courseid = :courseid';
        $searchsql = $DB->sql_like('name', ':query', false);
        $selectsql = "$courseselectsql AND $searchsql";

        // Do the query for exact match as case insensitive.
        $sqlparams = [
            'courseid' => $courseid,
            'query' => $query,
        ];
        $exactmatch = $DB->get_record_select('groups', $selectsql, $sqlparams);
        // If the query does not match exactly, we need to add an element for creating a new group.
        if (!$exactmatch) {
            $newelement = (object)[
                'id' => "NEW_$query",
                'name' => $query,
                'new' => true,
            ];
        }

        // Do the query for partial match to get all suggestions.
        $sqlparams = [
            'courseid' => $courseid,
            'query' => '%' . $query . '%',
        ];

        $records = $DB->get_records_select('groups', $selectsql, $sqlparams, 'name ASC', '*');

        $list = [];
        foreach ($records as $record) {
            $group = (object)[
                'id' => $record->id,
                'name' => $record->name,
                'new' => false,
            ];
            $list[$record->id] = $group;
        }

        if (!empty($newelement)) {
            $list = [0 => $newelement] + $list;
        }

        return [
            'list' => $list,
        ];

    }

    /**
     * Describes the external function result value.
     *
     * @return \external_description
     */
    public static function execute_returns(): \external_description {

        return new \external_single_structure([
            'list' => new \external_multiple_structure(
                new \external_single_structure([
                    'id' => new \external_value(PARAM_RAW, 'ID of the group'),
                    'name' => new \external_value(PARAM_RAW, 'The name of the group'),
                    'new' => new \external_value(PARAM_BOOL, 'The group is new'),
                ])
            ),
        ]);
    }
}
