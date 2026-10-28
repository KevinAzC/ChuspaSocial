<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_chuspasocial\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\request\approved_contextlist;
use core_tag_tag;

/**
 * Privacy provider for the ChuspaSocial plugin.
 *
 * @package    local_chuspasocial
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Returns the reason why this plugin does not store personal data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }

    /**
     * Deletes all the data of the plugin that is stored in a context.
     *
     * In a course context it deletes the posts of the course with their comments, reactions,
     * tags, reports and files, and the links between the book listings and the course.
     * In the system context it deletes the posts of the general wall in the same way,
     * and also the follows, the book listings and the reports.
     *
     * @param \context $context The context to delete the data from.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = (int) $context->instanceid;
        } else if ($context->contextlevel == CONTEXT_SYSTEM) {
            $courseid = 0;
        } else {
            return;
        }

        $params = ['courseid' => $courseid];
        $postselect = 'SELECT id FROM {local_chuspasocial_post} WHERE courseid = :courseid';
        $commentselect = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($postselect)";

        $transaction = $DB->start_delegated_transaction();

        try {
            // Delete the tags of the posts.
            $postids = $DB->get_fieldset_select('local_chuspasocial_post', 'id', 'courseid = :courseid', $params);
            foreach ($postids as $postid) {
                core_tag_tag::remove_all_item_tags('local_chuspasocial', 'local_chuspasocial_post', $postid);
            }

            // Delete the reports about the posts and their comments.
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "itemtype = 'comment' AND itemid IN ($commentselect)",
                $params
            );
            $DB->delete_records_select(
                'local_chuspasocial_report',
                "itemtype = 'post' AND itemid IN ($postselect)",
                $params
            );

            // Delete the reactions, the comments and the posts.
            $DB->delete_records_select('local_chuspasocial_reaction', "postid IN ($postselect)", $params);
            $DB->delete_records_select('local_chuspasocial_comment', "postid IN ($postselect)", $params);
            $DB->delete_records('local_chuspasocial_post', $params);

            // Delete the files of the posts.
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'local_chuspasocial', 'postimage');
            $fs->delete_area_files($context->id, 'local_chuspasocial', 'post');

            if ($courseid > 0) {
                // Delete the links between the book listings and the course.
                $DB->delete_records('local_chuspasocial_lstcourse', $params);
            } else {
                // Delete the data that belongs to the system context.
                $DB->delete_records('local_chuspasocial_follow');
                $DB->delete_records('local_chuspasocial_lstcourse');
                $DB->delete_records('local_chuspasocial_listing');
                $DB->delete_records('local_chuspasocial_report');
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * Deletes the data of one user in the approved contexts.
     *
     * In a course context it deletes the posts of the user in the course with their comments,
     * reactions, tags, reports and files, the comments and reactions of the user on other posts
     * of the course, and the reports made by the user about content of the course.
     * In the system context it does the same for the general wall, and also deletes the follows
     * of the user, the follows other users have of the user, the book listings of the user with
     * their photos and course links, and the reports made by the user about listings.
     *
     * @param approved_contextlist $contextlist The approved contexts and the user.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        if ($contextlist->count() == 0) {
            return;
        }

        $userid = (int) $contextlist->get_user()->id;
        $fs = get_file_storage();

        $transaction = $DB->start_delegated_transaction();

        try {
            foreach ($contextlist->get_contexts() as $context) {
                if ($context->contextlevel == CONTEXT_COURSE) {
                    $courseid = (int) $context->instanceid;
                } else if ($context->contextlevel == CONTEXT_SYSTEM) {
                    $courseid = 0;
                } else {
                    continue;
                }

                $params = ['userid' => $userid, 'courseid' => $courseid];
                $courseposts = 'SELECT id FROM {local_chuspasocial_post} WHERE courseid = :courseid';
                $ownposts = $courseposts . ' AND userid = :userid';
                $coursecomments = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($courseposts)";
                $owncomments = $coursecomments . ' AND userid = :userid';
                $postcomments = "SELECT id FROM {local_chuspasocial_comment} WHERE postid IN ($ownposts)";

                // Delete the tags and the files of the posts of the user.
                $postids = $DB->get_fieldset_select(
                    'local_chuspasocial_post',
                    'id',
                    'userid = :userid AND courseid = :courseid',
                    $params
                );
                foreach ($postids as $postid) {
                    core_tag_tag::remove_all_item_tags('local_chuspasocial', 'local_chuspasocial_post', $postid);
                    $fs->delete_area_files($context->id, 'local_chuspasocial', 'postimage', $postid);
                    $fs->delete_area_files($context->id, 'local_chuspasocial', 'post', $postid);
                }

                // Delete the reports about the posts of the user and their comments.
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "itemtype = 'comment' AND itemid IN ($postcomments)",
                    $params
                );
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "itemtype = 'post' AND itemid IN ($ownposts)",
                    $params
                );

                // Delete the reports about the comments of the user on other posts.
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "itemtype = 'comment' AND itemid IN ($owncomments)",
                    $params
                );

                // Delete the reports made by the user about content of this context.
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "reporterid = :userid AND itemtype = 'comment' AND itemid IN ($coursecomments)",
                    $params
                );
                $DB->delete_records_select(
                    'local_chuspasocial_report',
                    "reporterid = :userid AND itemtype = 'post' AND itemid IN ($courseposts)",
                    $params
                );

                // Delete the reactions and the comments on the posts of the user.
                $DB->delete_records_select('local_chuspasocial_reaction', "postid IN ($ownposts)", $params);
                $DB->delete_records_select('local_chuspasocial_comment', "postid IN ($ownposts)", $params);

                // Delete the reactions and the comments of the user on other posts.
                $DB->delete_records_select(
                    'local_chuspasocial_reaction',
                    "userid = :userid AND postid IN ($courseposts)",
                    $params
                );
                $DB->delete_records_select(
                    'local_chuspasocial_comment',
                    "userid = :userid AND postid IN ($courseposts)",
                    $params
                );

                // Delete the posts of the user.
                $DB->delete_records('local_chuspasocial_post', $params);

                if ($courseid == 0) {
                    // Delete the follows of the user and the follows other users have of the user.
                    $DB->delete_records('local_chuspasocial_follow', ['userid' => $userid]);
                    $DB->delete_records('local_chuspasocial_follow', ['itemtype' => 'user', 'itemid' => $userid]);

                    // Delete the book listings of the user with their photos and course links.
                    $listingids = $DB->get_fieldset_select('local_chuspasocial_listing', 'id', 'userid = :userid', $params);
                    foreach ($listingids as $listingid) {
                        $fs->delete_area_files($context->id, 'local_chuspasocial', 'photos', $listingid);
                    }
                    $ownlistings = 'SELECT id FROM {local_chuspasocial_listing} WHERE userid = :userid';
                    $DB->delete_records_select(
                        'local_chuspasocial_report',
                        "itemtype = 'listing' AND itemid IN ($ownlistings)",
                        $params
                    );
                    $DB->delete_records_select('local_chuspasocial_lstcourse', "listingid IN ($ownlistings)", $params);
                    $DB->delete_records('local_chuspasocial_listing', ['userid' => $userid]);

                    // Delete the reports made by the user about listings.
                    $DB->delete_records('local_chuspasocial_report', ['reporterid' => $userid, 'itemtype' => 'listing']);
                }
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
    }
}
