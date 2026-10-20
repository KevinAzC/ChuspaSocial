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
 * Service to query the book listings of the marketplace.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

/**
 * Service to query the book listings of the marketplace.
 *
 * @package    local_chuspasocial
 */
class listing_service {
    /**
     * Search listings by book title or author, case insensitive.
     *
     * The search text is always passed as a SQL parameter, never concatenated.
     *
     * @param string $q Text to search in the title or the author.
     * @param int $page Page number, starting at 0.
     * @param int $perpage Number of listings per page.
     * @return array List of listing records, newest first.
     */
    public static function search(string $q, int $page, int $perpage): array {
        global $DB;

        $titlelike = $DB->sql_like('l.title', ':title', false);
        $authorlike = $DB->sql_like('l.author', ':author', false);
        $pattern = '%' . $DB->sql_like_escape(trim($q)) . '%';

        $sql = "SELECT l.*
                  FROM {local_chuspasocial_listing} l
                 WHERE $titlelike OR $authorlike
              ORDER BY l.timecreated DESC, l.id DESC";
        $params = ['title' => $pattern, 'author' => $pattern];

        return array_values($DB->get_records_sql($sql, $params, $page * $perpage, $perpage));
    }

    /**
     * Get the available listings linked to the courses of the current user.
     *
     * A listing linked to several courses of the user is returned only once.
     *
     * @param int $page Page number, starting at 0.
     * @param int $perpage Number of listings per page.
     * @return array List of listing records, newest first.
     */
    public static function get_for_my_courses(int $page, int $perpage): array {
        global $DB;

        $courseids = array_keys(enrol_get_my_courses('id'));
        if (empty($courseids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'course');
        $params['status'] = 'available';

        $sql = "SELECT l.*
                  FROM {local_chuspasocial_listing} l
                 WHERE l.status = :status
                   AND EXISTS (
                       SELECT 1
                         FROM {local_chuspasocial_lstcourse} lc
                        WHERE lc.listingid = l.id
                          AND lc.courseid $insql
                   )
              ORDER BY l.timecreated DESC, l.id DESC";

        return array_values($DB->get_records_sql($sql, $params, $page * $perpage, $perpage));
    }
}
