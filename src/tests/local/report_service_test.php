<?php
namespace local_chuspasocial\local;

/**
 * Pruebas unitarias para report_service::create_report()
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\report_service::create_report
 */
class report_service_test extends \advanced_testcase {

    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_create_report_valido(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $data = (object) [
            'userid' => $user->id,
            'itemtype' => 'post',
            'itemid' => 101,
            'reason' => 'Contenido inapropiado',
        ];

        $report = report_service::create_report($data);

        $this->assertNotEmpty($report);
        $this->assertEquals($user->id, $report->userid);
        $this->assertEquals('post', $report->itemtype);
    }

    public function test_create_report_duplicado(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $data = (object) [
            'userid' => $user->id,
            'itemtype' => 'post',
            'itemid' => 101,
            'reason' => 'Primer reporte',
        ];

        // Se crea el primer reporte válido
        report_service::create_report($data);

        // Intentar crear exactamente el mismo reporte debe lanzar una excepción
        $this->expectException(\moodle_exception::class);
        report_service::create_report($data);
    }

    public function test_create_report_itemtype_invalido(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $data = (object) [
            'userid' => $user->id,
            'itemtype' => 'itemtype_no_existente',
            'itemid' => 101,
            'reason' => 'Prueba itemtype inválido',
        ];

        $this->expectException(\invalid_parameter_exception::class);
        report_service::create_report($data);
    }

}