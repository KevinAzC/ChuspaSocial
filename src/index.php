<?php
// This file is part of ChuspaSocial


require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chuspasocial/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_chuspasocial'));
$PAGE->set_heading(get_string('pluginname', 'local_chuspasocial'));

// Cargar el módulo AMD del muro solicitado en el issue #312
$PAGE->requires->js_call_amd('local_chuspasocial/wall', 'init');

echo $OUTPUT->header();

// Cuerpo temporal o contenido del muro
echo $OUTPUT->footer();
