<?php

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use local_chuspasocial\local\persistent\post;
use moodle_exception;

/**
 * Servicio para la gestion de publicaciones (posts).
 *
 * @package local_chuspasocial
 */
class post_service {

    /**
     * Actualiza el contenido y formato de un post existente.
     *
     * @param int $postid ID de la publicacion a actualizar.
     * @param string $content Nuevo contenido.
     * @param int $format Formato del contenido.
     * @throws moodle_exception Si el usuario actual no es el autor.
     */
    public static function update_post(int $postid, string $content, int $format): void {
        global $USER;

        // Instanciar la publicacion existente.
        $post = new post($postid);

        // Verificar que el usuario actual sea el autor.
        if ((int) $post->get('userid') !== (int) $USER->id) {
            throw new moodle_exception(
                'cannotupdatepost',
                'local_chuspasocial',
                '',
                null,
                'Solo el autor puede editar esta publicacion.'
            );
        }

        // Actualizar los datos.
        $post->set('content', $content);
        $post->set('format', $format);
        $post->set('timemodified', time());
        $post->set('usermodified', $USER->id);

        // Guardar los cambios.
        $post->update();
    }

    /**
     * Elimina una publicacion, sus comentarios, reacciones y archivos.
     *
     * @param int $postid ID de la publicacion que se eliminara.
     * @throws \moodle_exception Si la publicacion no existe o el usuario no esta autorizado.
     */
    public static function delete_post(int $postid): void {
        global $DB, $USER;

        // Buscar la publicacion.
        $post = $DB->get_record(
            'local_chuspasocial_post',
            ['id' => $postid],
            '*',
            MUST_EXIST
        );

        // Determinar el contexto de la publicacion.
        if ((int) $post->courseid > 0) {
            $context = \context_course::instance((int) $post->courseid);
        } else {
            $context = \context_system::instance();
        }

        // Verificar si el usuario es el autor.
        $isauthor = (int) $post->userid === (int) $USER->id;

        // Comprobar el permiso de moderacion solo si esta definido.
        $ismoderator = false;
        if (get_capability_info('local/chuspasocial:moderate')) {
            $ismoderator = has_capability(
                'local/chuspasocial:moderate',
                \context_system::instance()
            );
        }

        // Solo el autor o un moderador pueden eliminar la publicacion.
        if (!$isauthor && !$ismoderator) {
            throw new \moodle_exception(
                'nopermissions',
                'error',
                '',
                'delete this post'
            );
        }

        // Iniciar la transaccion.
        $transaction = $DB->start_delegated_transaction();

        try {
            // Eliminar los comentarios de la publicacion.
            $DB->delete_records(
                'local_chuspasocial_comment',
                ['postid' => $postid]
            );

            // Eliminar las reacciones de la publicacion.
            $DB->delete_records(
                'local_chuspasocial_reaction',
                ['postid' => $postid]
            );

            // Eliminar los archivos asociados a la publicacion.
            get_file_storage()->delete_area_files(
                $context->id,
                'local_chuspasocial',
                'post',
                $postid
            );

            // Eliminar la publicacion.
            $DB->delete_records(
                'local_chuspasocial_post',
                ['id' => $postid]
            );

            // Preparar el evento de eliminacion.
            $event = \local_chuspasocial\event\post_deleted::create([
                'objectid' => $postid,
                'context' => $context,
                'userid' => $USER->id,
            ]);

            // Confirmar los cambios de la base de datos.
            $transaction->allow_commit();

        } catch (\Throwable $e) {
            // Revertir los cambios de la transaccion si hay un error.
            $transaction->rollback($e);
        }

        // Registrar el evento despues de confirmar la transaccion.
        $event->trigger();
    }
}
