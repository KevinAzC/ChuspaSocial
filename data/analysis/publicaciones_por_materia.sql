-- =========================================================
-- Consulta para medir métricas de éxito: publicaciones por materia y semana
-- Para cada materia (courseid) y semana del año, cuenta el total de
-- publicaciones (total_publicaciones), ordenadas de mayor a menor
-- actividad, para medir la adopción y la actividad académica en la
-- plataforma.
-- Utiliza la sintaxis estándar de Moodle {tabla} para los prefijos.
--
-- Tablas: local_chuspasocial_post (campos courseid, timecreated) y
-- course, del núcleo de Moodle (campos id, shortname).
--
-- No expone datos personales: solo identificadores y nombres de curso
-- (courseid, shortname) y agregados por materia y semana.
-- =========================================================

SELECT 
    c.id AS courseid,
    c.shortname AS materia,
    EXTRACT(YEAR FROM TO_TIMESTAMP(p.timecreated)) AS anio,
    EXTRACT(WEEK FROM TO_TIMESTAMP(p.timecreated)) AS semana,
    COUNT(p.id) AS total_publicaciones
FROM 
    {local_chuspasocial_post} p
JOIN 
    {course} c ON c.id = p.courseid
GROUP BY 
    c.id,
    c.shortname,
    EXTRACT(YEAR FROM TO_TIMESTAMP(p.timecreated)),
    EXTRACT(WEEK FROM TO_TIMESTAMP(p.timecreated))
ORDER BY
    total_publicaciones DESC,
    anio DESC,
    semana DESC;

/*
=========================================
EJEMPLO DE RESULTADO (Criterio de aceptación)
=========================================
courseid | materia | anio | semana | total_publicaciones
-------------------------------------------------------
2        | INF-111 | 2026 | 41     | 45
2        | INF-111 | 2026 | 40     | 39
5        | MAT-115 | 2026 | 41     | 28
8        | LIN-111 | 2026 | 40     | 12
=========================================
*/
