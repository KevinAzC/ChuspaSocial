-- =========================================================
-- Consulta: tags mas usados por mes en ChuspaSocial
-- Issue #20
--
-- Relaciona las etiquetas de Moodle con las publicaciones
-- de ChuspaSocial mediante tag_instance.
--
-- Supuestos:
--   tag_instance.itemtype = 'local_chuspasocial_post'
--   tag_instance.component = 'local_chuspasocial'
--   local_chuspasocial_post.id identifica cada publicacion
--   local_chuspasocial_post.timecreated almacena su fecha
--
-- Solo devuelve datos agregados; no expone datos personales.
-- =========================================================

SELECT
    EXTRACT(YEAR FROM TO_TIMESTAMP(p.timecreated)) AS anio,
    EXTRACT(MONTH FROM TO_TIMESTAMP(p.timecreated)) AS mes,
    t.id AS tagid,
    t.name AS tag,
    COUNT(*) AS total_usos
FROM tag_instance ti
INNER JOIN tag t
    ON t.id = ti.tagid
INNER JOIN local_chuspasocial_post p
    ON p.id = ti.itemid
WHERE
    ti.component = 'local_chuspasocial'
    AND ti.itemtype = 'local_chuspasocial_post'
GROUP BY
    EXTRACT(YEAR FROM TO_TIMESTAMP(p.timecreated)),
    EXTRACT(MONTH FROM TO_TIMESTAMP(p.timecreated)),
    t.id,
    t.name
ORDER BY
    anio,
    mes,
    total_usos DESC;

/*
=========================================
EJEMPLO DE RESULTADO (datos ficticios)
=========================================
anio | mes | tagid | tag          | total_usos
-----------------------------------------------
2026 | 7   | 3     | literatura   | 18
2026 | 7   | 5     | novela       | 12
2026 | 8   | 3     | literatura   | 24
2026 | 8   | 8     | ciencia      | 15
2026 | 9   | 5     | novela       | 20
=========================================
*/