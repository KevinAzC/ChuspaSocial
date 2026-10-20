# Entorno de desarrollo

## Problemas comunes

### Error `max_input_vars`

**Síntoma**

Durante la instalación o comprobación del entorno, Moodle muestra el mensaje:

`PHP setting max_input_vars must be at least 5000.`

**Causa**

La directiva `max_input_vars` de PHP está configurada con un valor menor que `5000`. Moodle necesita al menos ese límite para procesar correctamente formularios con una gran cantidad de campos.

**Solución**

1. Localiza el archivo `php.ini` utilizado por el servidor web.
2. Busca la directiva `max_input_vars` y establece:

   ```ini
   max_input_vars = 5000
   ```

3. Guarda el archivo y reinicia el servicio web o PHP para aplicar el cambio.
4. Verifica el valor activo con:

   ```bash
   php -i | grep max_input_vars
   ```

El valor efectivo debe ser `5000` o superior.
