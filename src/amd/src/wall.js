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
 * Wall module.
 *
 * @module     local_chuspasocial/wall
 * @copyright  2026 Kevin Zapata Cabezas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

/**
 * Focus the first new post inside the given container.
 *
 * @param {HTMLElement|jQuery} container Container with the new posts.
 */
const focusFirstNewPost = (container) => {
    const $newPost = $(container).find('.post-item, article').first();

    if ($newPost.length) {
        if (!$newPost.is('a, button, input, [tabindex]')) {
            $newPost.attr('tabindex', '-1');
        }
        $newPost.focus();
    }
};

/**
 * Initialise the wall module.
 */
export const init = () => {
    $(document).on('click', '[data-action="load-more"]', () => {
        $(document).one('wall:posts-loaded', (e, data) => {
            if (data && data.content) {
                focusFirstNewPost(data.content);
            }
        });
    });
};
