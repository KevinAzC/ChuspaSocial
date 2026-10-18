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
 * Service for managing reactions.
 *
 * @package    local_chuspasocial
 * @copyright  2026 Kevin Zapata Cabezas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Class reaction_service
 *
 * @package    local_chuspasocial
 */
class reaction_service {

    /**
     * Counts the total reactions for a list of post IDs using a single query.
     *
     * @param array $postids List of post IDs.
     * @return array Array mapping postid => total count.
     */
    public static function count_for_posts(array $postids): array {
        global $DB;

        // Initialize array with 0 for all requested post IDs.
        $result = array_fill_keys($postids, 0);

        if (empty($postids)) {
            return $result;
        }

        // Clean post IDs to ensure integers.
        $postids = array_map('intval', $postids);

        list($insql, $params) = $DB->get_in_or_equal($postids, SQL_PARAMS_NAMED);

        $sql = "SELECT postid, COUNT(id) AS total
                  FROM {chuspasocial_reactions}
                 WHERE postid $insql
              GROUP BY postid";

        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $record) {
            $result[$record->postid] = (int) $record->total;
        }

        return $result;
    }

    /**
     * Checks if a user has reacted to a list of posts in a single query.
     *
     * @param array $postids List of post IDs.
     * @param int $userid User ID. If 0, defaults to current $USER->id.
     * @return array Array mapping postid => bool.
     */
    public static function has_reacted_many(array $postids, int $userid = 0): array {
        global $DB, $USER;

        $result = array_fill_keys($postids, false);

        if (empty($postids)) {
            return $result;
        }

        if ($userid === 0) {
            $userid = (int) $USER->id;
        }

        if (empty($userid)) {
            return $result;
        }

        $postids = array_map('intval', $postids);

        list($insql, $params) = $DB->get_in_or_equal($postids, SQL_PARAMS_NAMED);
        $params['userid'] = $userid;

        $sql = "SELECT postid
                  FROM {chuspasocial_reactions}
                 WHERE postid $insql AND userid = :userid
              GROUP BY postid";

        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $record) {
            $result[$record->postid] = true;
        }

        return $result;
    }
}
