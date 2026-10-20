-- =========================================================
-- Consulta para medir métricas de éxito: publicaciones por materia y semana
-- Permite analizar la adopción y actividad académica en la plataforma.
-- Utiliza la sintaxis estándar de Moodle {tabla} para los prefijos.
-- No expone datos personales (solo identificadores de curso y agregados).
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
    anio DESC,
    semana DESC,
    total_publicaciones DESC;

/*
=========================================
EJEMPLO DE RESULTADO (Criterio de aceptación)
=========================================
courseid | materia | anio | semana | total_publicaciones
-------------------------------------------------------
2        | INF-111 | 2026 | 41     | 45
5        | MAT-115 | 2026 | 41     | 28
2        | INF-111 | 2026 | 40     | 39
8        | LIN-111 | 2026 | 40     | 12
=========================================
*/
