# Documentación de API

## Introducción

ChuspaSocial expone sus funciones mediante el sistema de **servicios web de Moodle** (External Functions API), no mediante endpoints REST tradicionales. Cada función externa se registra en Moodle y puede invocarse de dos formas:

### 1. Desde el propio Moodle (AJAX)

Cuando el usuario ya tiene una sesión iniciada en Moodle, el frontend del plugin llama a las funciones externas mediante el módulo `core/ajax`, sin necesidad de token ni URL pública. Ejemplo desde JavaScript (AMD):

```javascript
import Ajax from 'core/ajax';

const request = {
    methodname: 'local_chuspasocial_toggle_follow',
    args: {
        component: 'user',
        itemid: 123
    }
};

Ajax.call([request])[0].then((response) => {
    console.log(response);
});
```

### 2. Desde fuera de Moodle (REST con token)

Para integraciones externas (apps móviles, scripts, otros sistemas), las funciones se llaman vía REST usando un **token de servicio web** generado para el usuario. El formato general es:

https://<tu-sitio-moodle>/webservice/rest/server.php
?wstoken=<TOKEN>
&wsfunction=<nombre_de_la_funcion>
&moodlewsrestformat=json
&<parametros_de_la_funcion>


Por ejemplo, para llamar a `local_chuspasocial_toggle_follow`:

https://<tu-sitio-moodle>/webservice/rest/server.php
?wstoken=<TOKEN>
&wsfunction=local_chuspasocial_toggle_follow
&moodlewsrestformat=json
&component=user
&itemid=123


El token se genera y gestiona desde **Administración del sitio → Servidor → Servicios web → Gestionar tokens**, y requiere que el usuario tenga las capacidades correspondientes habilitadas.

## Funciones Externas

### `local_chuspasocial_toggle_follow`

Permite seguir o dejar de seguir a un usuario o etiqueta (*tag*) sin necesidad de recargar la página.

**Descripción:**  
Alterna el estado de seguimiento (*follow/unfollow*) para un usuario o etiqueta especificados dentro de la plataforma.

**Parámetros:**  
* `component` (string): Tipo de objetivo a seguir. Valores permitidos: `'user'` o `'tag'`.
* `itemid` (int): ID del usuario o del tag al que se desea seguir o dejar de seguir.

**Capacidades requeridas:**  
* `local/chuspasocial:follow`

**Respuesta de ejemplo (JSON):**  
```json
{
  "status": true,
  "action": "followed",
  "message": "Ahora sigues a este usuario."
}
```

## Errores

Las funciones externas de Moodle no devuelven códigos HTTP tradicionales para errores de lógica de negocio. En su lugar, lanzan excepciones de tipo `moodle_exception` (o subclases como `invalid_parameter_exception`, `required_capability_exception`), que el cliente recibe como un objeto JSON con esta forma:

```json
{
  "exception": "moodle_exception",
  "errorcode": "nopermissions",
  "message": "No tienes permisos para realizar esta acción."
}
```

Casos comunes:
* **`invalid_parameter_exception`**: un parámetro no cumple la validación definida en `execute_parameters()`.
* **`required_capability_exception`** / **`nopermissions`**: el usuario no tiene la capacidad requerida.
* **`require_login`**: el usuario no tiene sesión iniciada.
