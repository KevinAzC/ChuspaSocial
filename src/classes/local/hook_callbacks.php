<?php

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use core\hook\navigation\primary_extend;

/**
 * Callbacks de hooks de Moodle para ChuspaSocial.
 *
 * @package    local_chuspasocial
 */
class hook_callbacks {

    /**
     * Agrega el nodo "ChuspaSocial" a la navegacion principal.
     *
     * Solo se muestra a usuarios autenticados (no a invitados).
     *
     * @param primary_extend $hook Hook de extension de la navegacion principal.
     */
    public static function extend_primary_navigation(primary_extend $hook): void {
        if (!isloggedin() || isguestuser()) {
            return;
        }

        $hook->get_primaryview()->add(
            get_string('pluginname', 'local_chuspasocial'),
            new \moodle_url('/local/chuspasocial/index.php'),
            \navigation_node::TYPE_CUSTOM,
            null,
            'chuspasocial'
        );
    }
}