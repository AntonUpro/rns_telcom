<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Круг стальной горячекатаный ГОСТ 2590-2006.
 * Диаметры 5–270 мм (основной ряд).
 * Все характеристики вычислены аналитически из диаметра.
 *
 * Формулы (d — диаметр в см):
 *   A   = π·d²/4                   см²
 *   m   = A·0.785                  кг/м
 *   I   = π·d⁴/64                  см⁴
 *   i   = d/4                      см
 *   W   = π·d³/32                  см³
 *   Ip  = π·d⁴/32 = 2·I            см⁴
 *   Wp  = π·d³/16                  см³
 *   Wpl = d³/6                     см³
 */
final class Version20260605120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Сортамент круглого проката ГОСТ 2590-2006 (d=5–270 мм)';
    }

    public function up(Schema $schema): void
    {
        // type_id=7 → CIRCLE
        // d_vals — диаметры в мм по основному ряду ГОСТ 2590-2006
        $this->addSql("
            WITH diameters(d_mm) AS (
                VALUES
                (5),(6),(7),(8),(9),(10),(11),(12),(13),(14),(15),(16),(17),(18),(19),(20),
                (21),(22),(23),(24),(25),(26),(27),(28),(29),(30),(31),(32),(33),(34),(35),
                (36),(38),(40),(42),(45),(48),(50),(52),(55),(56),(58),(60),(63),(65),(67),
                (70),(75),(80),(85),(90),(95),(100),(105),(110),(115),(120),(125),(130),
                (135),(140),(145),(150),(155),(160),(165),(170),(175),(180),(185),(190),
                (195),(200),(210),(220),(230),(240),(250),(260),(270)
            ),
            computed AS (
                SELECT
                    d_mm,
                    ROUND((PI() * (d_mm/10.0)^2 / 4)::numeric, 3)            AS area,
                    ROUND((PI() * (d_mm/10.0)^2 / 4 * 0.785)::numeric, 3)    AS mass,
                    ROUND((PI() * (d_mm/10.0)^4 / 64)::numeric, 2)           AS inertia,
                    ROUND(((d_mm/10.0) / 4)::numeric, 2)                      AS r_inertia,
                    ROUND((PI() * (d_mm/10.0)^3 / 32)::numeric, 2)           AS resistance,
                    ROUND((PI() * (d_mm/10.0)^4 / 32)::numeric, 2)           AS polar_i,
                    ROUND((PI() * (d_mm/10.0)^3 / 16)::numeric, 2)           AS polar_w,
                    ROUND(((d_mm/10.0)^3 / 6)::numeric, 2)                   AS plastic_w
                FROM diameters
            ),
            ins AS (
                INSERT INTO gauge_profile (type_id, name, designation, standard, is_custom)
                SELECT
                    7,
                    'Круг ' || d_mm,
                    d_mm::text,
                    'ГОСТ 2590-2006',
                    false
                FROM computed
                RETURNING id, designation
            )
            INSERT INTO gauge_round_solid
                (profile_id, diameter,
                 area, mass_per_meter,
                 moment_inertia, radius_inertia, moment_resistance,
                 polar_moment_inertia, polar_moment_resistance, plastic_moment_resistance)
            SELECT
                ins.id,
                c.d_mm,
                c.area, c.mass,
                c.inertia, c.r_inertia, c.resistance,
                c.polar_i, c.polar_w, c.plastic_w
            FROM ins
            JOIN computed c ON ins.designation = c.d_mm::text
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM gauge_profile
            WHERE type_id = 7 AND standard = 'ГОСТ 2590-2006' AND is_custom = false
        ");
    }
}
