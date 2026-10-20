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

### `local_chuspasocial_get_listings`

Permite consultar los libros publicados en el marketplace: los de las materias del usuario o los que coinciden con una búsqueda.

**Descripción:**  
Si no se envía texto de búsqueda, devuelve los anuncios disponibles vinculados a las materias en las que está inscrito el usuario. Si se envía texto, devuelve los anuncios cuyo título o autor coinciden con él. Los resultados se devuelven paginados.

**Parámetros:**  
* `search` (string, opcional): Texto a buscar en el título o el autor del libro. Por defecto `''`; si está vacío, se devuelven los libros de mis materias.
* `page` (int, opcional): Número de página, empezando en `0`. Por defecto `0`.
* `perpage` (int, opcional): Cantidad de anuncios por página, entre `1` y `50`. Por defecto `20`.

**Capacidades requeridas:**  
* `local/chuspasocial:view`

**Respuesta de ejemplo (JSON):**  
```json
{
  "listings": [
    {
      "id": 2,
      "userid": 2,
      "title": "Fisica",
      "author": "Serway",
      "description": "<p>Buen estado</p>",
      "descriptionformat": 1,
      "price": 120.5,
      "externalcontact": "70000000",
      "status": "available",
      "timecreated": 1760000100
    }
  ]
}
```

Campos de cada anuncio: `id` (id del anuncio), `userid` (id del vendedor), `title`, `author`, `description` (descripción ya formateada), `descriptionformat`, `price`, `externalcontact` (contacto externo; vacío si no se indicó), `status` (`available` o `sold`) y `timecreated` (marca de tiempo Unix).

**Errores:**  
* `invalid_parameter_exception`: `page` es negativo o `perpage` está fuera del rango de 1 a 50.
* `required_capability_exception`: el usuario no tiene la capacidad `local/chuspasocial:view`.
* `require_login`: el usuario no tiene sesión iniciada.

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
