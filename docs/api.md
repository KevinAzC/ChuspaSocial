
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

## Códigos de error

| Código | Descripción |
|---|---|
| 200 | OK |
| 400 | Bad Request |
| 401 | Not Logged In |
| 403 | No Permissions |
| 500 | Internal Server Error |
