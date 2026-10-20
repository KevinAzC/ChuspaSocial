<?php

namespace local_chuspasocial\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;

/**
 * Formulario para la publicacion de un libro (listing_form)
 * 
 * @package    local_chuspasocial
 */
class listing_form extends moodleform {
    /**
     * Define los campos de formulario
     */
    public function definition() {
        $mform = $this->_form;

        // Titulo del libro
        $mform->addelement('text', 'title', get_string('title', 'local_chuspasocial'));
        $mform->setType('title', PARAM_TEXT); 
        $mform->addRule('title', null, 'required', null, 'client');

        // Autor del libro
        $mform->addelement('text', 'author', get_string('author', 'local_chuspasocial'));
        $mform->setType('author', PARAM_TEXT); 
        $mform->addRule('author', null, 'required', null, 'client');

        // Descripcion con editor HTML
        $mform->addelement('editor', 'description', get_string('description', 'local_chuspasocial'));
        $mform->setType('description', PARAM_RAW); 

        // Precio
        $mform->addelement('text', 'price', get_string('price', 'local_chuspasocial'));
        $mform->setType('price', PARAM_TEXT); 
        $mform->addRule('price', null, 'required', null, 'client');

        // Materias asociadas al anuncio.
        $courses = enrol_get_my_courses(['id', 'fullname']);
        $courseoptions = [];
        foreach ($courses as $course) {
            $courseoptions[$course->id] = format_string($course->fullname);
        }

        $mform->addElement(
            'autocomplete',
            'course',
            get_string('courses'),
            $courseoptions,
            ['multiple' => true]
        );

        // Contacto externo (opcional)
        $mform->addelement('text', 'contact', get_string('contact', 'local_chuspasocial'));
        $mform->setType('contact', PARAM_TEXT); 

        // Botones de accion (Guardar/Cancelar)
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validacion personalizada de formulario
     * 
     * @param   array $data Datos enviados por el formulario 
     * @param   array $files Archivos subidos
     * @return  array Errores encontrados
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Criterio de aceptacion: Validar precio >= 0
        if(isset($data['price'])) {
            if(!is_numeric($data['price']) || (float)$data['price'] < 0) {
                $errors['price'] = get_string('errorinvalidprice', 'local_chuspasocial', 'El precio debe ser un numero mayor o igual a 0.');
            }
        }

        return $errors;
    }
}

