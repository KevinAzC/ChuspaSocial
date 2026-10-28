# Revisión de control de acceso: require_login en las páginas

**Fecha:** 2026-10-18
**Rama:** security/audit-login
**Alcance:** todos los archivos PHP del plugin (`src/`).
**Método:** para cada archivo PHP se comprobó si es una página que se pueda pedir
directamente por HTTP y, si lo es, si llama a `require_login()` o
`require_course_login()` antes de mostrar cualquier contenido. La búsqueda de
esas funciones se hizo sobre todo `src/`:

    grep -rn require_login src/  →  src/index.php:27:require_login();

## Resumen

- Archivos PHP revisados: 23.
- Páginas accesibles directamente: 1.
- Páginas con `require_login()`: 1.
- Páginas sin verificación de sesión: 0.
- Llamadas a `require_course_login()`: 0.
- Problemas encontrados: ninguno.

## Páginas revisadas

| Archivo | Línea | Estado |
|---|---|---|
| src/index.php | 27 | OK: `require_login()` al inicio, antes de `$OUTPUT->header()` y de cualquier salida. |

`src/index.php` es el único script del plugin que se puede pedir directamente y
todas sus rutas de acceso pasan por él.

## Archivos revisados que no son páginas

| Archivo | Tipo | Estado |
|---|---|---|
| src/settings.php | Ajustes de administración | No aplica: lo incluye `admin/settings.php` de Moodle, que ya llama a `require_login(0, false)` (línea 11). |
| src/version.php | Declarativo | No aplica: lo lee Moodle, no se pide por HTTP. |
| src/db/access.php | Definición de capacidades | No aplica: lo carga Moodle y arranca con `defined('MOODLE_INTERNAL') || die();`. |
| src/db/events.php | Registro de eventos | No aplica: lo carga Moodle y arranca con `defined('MOODLE_INTERNAL') || die();`. |
| src/db/messages.php | Declaración de mensajes | No aplica: lo carga Moodle y arranca con `defined('MOODLE_INTERNAL') || die();`. |
| src/db/services.php | Declaración de servicios web | No aplica: lo carga Moodle y arranca con `defined('MOODLE_INTERNAL') || die();`. |
| src/db/tag.php | Definición de áreas de tag | No aplica: lo carga Moodle y arranca con `defined('MOODLE_INTERNAL') || die();`. |
| src/lang/en/local_chuspasocial.php | Cadenas de idioma | No aplica: lo carga Moodle y arranca con `defined('MOODLE_INTERNAL') || die();`. |
| src/lang/es/local_chuspasocial.php | Cadenas de idioma | No aplica: lo carga Moodle y arranca con `defined('MOODLE_INTERNAL') || die();`. |
| src/classes/event/post_created.php | Clase (evento) | No aplica: solo declara la clase; no tiene código de entrada. |
| src/classes/event/post_deleted.php | Clase (evento) | No aplica: solo declara la clase; no tiene código de entrada. |
| src/classes/event/comment_created.php | Clase (evento) | No aplica: solo declara la clase; no tiene código de entrada. |
| src/classes/event/listing_created.php | Clase (evento) | No aplica: solo declara la clase; no tiene código de entrada. |
| src/classes/event/reaction_added.php | Clase (evento) | No aplica: solo declara la clase; no tiene código de entrada. |
| src/classes/form/post_form.php | Clase (formulario) | No aplica: solo declara la clase; se instancia desde una página. |
| src/classes/form/listing_form.php | Clase (formulario) | No aplica: solo declara la clase; se instancia desde una página. |
| src/classes/local/hook_callbacks.php | Clase (callbacks de hooks) | No aplica: solo declara la clase; la llaman los hooks de Moodle. |
| src/classes/local/post_service.php | Clase (servicio) | No aplica: solo declara la clase; la llama una página o un servicio web. |
| src/classes/local/listing_service.php | Clase (servicio) | No aplica: solo declara la clase; la llama una página o un servicio web. |
| src/tests/lib_test.php | Prueba PHPUnit | No aplica: se ejecuta solo con PHPUnit. |
| src/tests/local/post_service_test.php | Prueba PHPUnit | No aplica: se ejecuta solo con PHPUnit. |
| src/tests/local/persistent/report_test.php | Prueba PHPUnit | No aplica: se ejecuta solo con PHPUnit. |
