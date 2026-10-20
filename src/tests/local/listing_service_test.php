<?php
// This file is part of Moodle - http://moodle.org
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
// along with Moodle.  If not, see <http://gnu.org>.

/**
 * Pruebas unitarias para listing_service::delete_listing().
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 sis-inf
 * @license    http://gnu.org GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

namespace local_chuspasocial\local;

use advanced_testcase;

/**
 * Clase de pruebas para el servicio de anuncios.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 sis-inf
 * @license    http://gnu.org GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\listing_service::delete_listing
 */
class listing_service_test extends advanced_testcase {

    /**
     * Configuración inicial antes de cada prueba.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Prueba que el autor original de un anuncio puede borrarlo con éxito.
     */
    public function test_delete_listing_as_author(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $author = $generator->create_user();

        $plugingenerator = $generator->get_plugin_generator('local_chuspasocial');
        $listing = $plugingenerator->create_listing(['userid' => $author->id]);

        $this->setUser($author);

        $result = listing_service::delete_listing($listing->id);
        $this->assertTrue($result);

        $this->assertFalse($DB->record_exists('local_chuspasocial_listing', ['id' => $listing->id]));
    }

    /**
     * Prueba que un usuario con rol de moderador puede borrar cualquier anuncio.
     */
    public function test_delete_listing_as_moderator(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $author = $generator->create_user();
        $moderator = $generator->create_user();

        $plugingenerator = $generator->get_plugin_generator('local_chuspasocial');
        $listing = $plugingenerator->create_listing(['userid' => $author->id]);

        $roleid = $generator->create_role();
        assign_capability('local/chuspasocial:moderate', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $moderator->id, \context_system::instance()->id);

        $this->setUser($moderator);

        $result = listing_service::delete_listing($listing->id);
        $this->assertTrue($result);

        $this->assertFalse($DB->record_exists('local_chuspasocial_listing', ['id' => $listing->id]));
    }

    /**
     * Prueba que al borrar un anuncio también se eliminan sus vínculos asociados.
     */
    public function test_delete_listing_removes_links(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $author = $generator->create_user();
        $course = $generator->create_course();

        $plugingenerator = $generator->get_plugin_generator('local_chuspasocial');
        $listing = $plugingenerator->create_listing(['userid' => $author->id]);

        $linkid = $DB->insert_record('local_chuspasocial_lstcourse', (object) [
            'listingid' => $listing->id,
            'courseid' => $course->id,
            'timecreated' => time()
        ]);

        $this->setUser($author);

        $result = listing_service::delete_listing($listing->id);
        $this->assertTrue($result);

        $this->assertFalse($DB->record_exists('local_chuspasocial_listing', ['id' => $listing->id]));
        $this->assertFalse($DB->record_exists('local_chuspasocial_lstcourse', ['id' => $linkid]));
    }
}
