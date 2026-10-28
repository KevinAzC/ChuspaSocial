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

/**
 * Service to manage the reactions of the ChuspaSocial posts.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

use context_course;
use context_system;
use local_chuspasocial\local\persistent\post;
use local_chuspasocial\local\persistent\reaction;
use moodle_exception;

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

    /**
     * Toggles the current user's reaction to a post.
     *
     * @param int $postid ID of the post to react to.
     * @return bool True if the reaction was added, false if it was removed.
     * @throws moodle_exception If the user cannot view the post.
     */
    public static function toggle(int $postid): bool {
        global $DB, $USER;

        $post = new post($postid);

        if (!post_service::can_view($post, (int) $USER->id)) {
            throw new moodle_exception('nopermissions', 'error', '', 'view this post');
        }

        // Hidden posts cannot receive reactions, even from their author.
        if ((int) $post->get('hidden') === 1) {
            throw new moodle_exception('nopermissions', 'error', '', 'react to this post');
        }

        if ((int) $post->get('courseid') > 0) {
            $context = context_course::instance((int) $post->get('courseid'));
        } else {
            $context = context_system::instance();
        }

        // The capability may not be defined yet (issue #256).
        if (get_capability_info('local/chuspasocial:react')) {
            require_capability('local/chuspasocial:react', $context);
        }

        $params = [
            'postid' => $postid,
            'userid' => (int) $USER->id,
        ];

        $existing = $DB->get_record('local_chuspasocial_reaction', $params);
        if ($existing) {
            $DB->delete_records('local_chuspasocial_reaction', ['id' => $existing->id]);
            return false;
        }

        $record = (object) [
            'postid' => $postid,
            'userid' => (int) $USER->id,
            'type' => reaction::TYPE_LIKE,
            'timecreated' => time(),
        ];
        $DB->insert_record('local_chuspasocial_reaction', $record);

        return true;
    }
}