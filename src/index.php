<?php
// This file is part of ChuspaSocial.

require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chuspasocial/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_chuspasocial'));
$PAGE->set_heading(get_string('pluginname', 'local_chuspasocial'));

echo $OUTPUT->header();

// Renderizar la plantilla wall según el issue #314
echo $OUTPUT->render_from_template('local_chuspasocial/wall', []);

echo $OUTPUT->footer();
