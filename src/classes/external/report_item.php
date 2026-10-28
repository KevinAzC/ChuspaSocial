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
 * External function local_chuspasocial_report_item.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_chuspasocial\local\report_service;

/**
 * Reports an item for moderation.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_item extends external_api {

    /**
     * Describes the parameters of the function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'itemtype' => new external_value(PARAM_ALPHA, 'Item type: post, comment or listing.'),
            'itemid' => new external_value(PARAM_INT, 'Reported item id.'),
            'reason' => new external_value(PARAM_RAW_TRIMMED, 'Reason for the report.'),
        ]);
    }

    /**
     * Reports an item for moderation.
     *
     * @param string $itemtype Item type.
     * @param int $itemid Reported item id.
     * @param string $reason Reason for the report.
     * @return array
     */
    public static function execute(string $itemtype, int $itemid, string $reason): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'itemtype' => $itemtype,
            'itemid' => $itemid,
            'reason' => $reason,
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/chuspasocial:report', $context);

        report_service::create_report(
            $params['itemtype'],
            (int) $params['itemid'],
            $params['reason']
        );

        return ['status' => true];
    }

    /**
     * Describes the return value of the function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Whether the report was created.'),
        ]);
    }
}
