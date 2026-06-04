<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Трубы профильные квадратные ГОСТ 30245-2003.
 * Все характеристики вычисляются из наружного размера стороны B и толщины стенки t.
 *
 * Обозначение: «BxT» (мм), напр. «40x2».
 * Наименование: «Труба кв. 40×2».
 *
 * Формулы (B, t в мм; d_cm = (B−2t)/10, B_cm = B/10):
 *   A   = B_cm² − d_cm²                                         см²
 *   m   = A · 0.785                                              кг/м
 *   I   = (B_cm⁴ − d_cm⁴) / 12                                  см⁴
 *   i   = √(I/A)                                                 см
 *   W   = 2·I / B_cm                                             см³
 *   Wpl = (B_cm³ − d_cm³) / 4                                    см³
 */
final class Version20260605130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Сортамент профильных труб квадратных ГОСТ 30245-2003';
    }

    public function up(Schema $schema): void
    {
        // type_id=6 → PIPE_SQUARE
        // (b_mm, t_mm) — пары «сторона × толщина стенки» по ГОСТ 30245-2003
        $this->addSql("
            WITH sizes(b_mm, t_mm) AS (
                VALUES
                -- 40
                (40,2),(40,3),(40,4),
                -- 50
                (50,2),(50,3),(50,4),(50,5),
                -- 60
                (60,2),(60,3),(60,4),(60,5),
                -- 70
                (70,3),(70,4),(70,5),
                -- 80
                (80,3),(80,4),(80,5),(80,6),
                -- 90
                (90,3),(90,4),(90,5),(90,6),
                -- 100
                (100,4),(100,5),(100,6),(100,8),
                -- 120
                (120,4),(120,5),(120,6),(120,8),
                -- 140
                (140,5),(140,6),(140,8),(140,10),
                -- 160
                (160,5),(160,6),(160,8),(160,10),
                -- 180
                (180,5),(180,6),(180,8),(180,10),(180,12),
                -- 200
                (200,6),(200,8),(200,10),(200,12),
                -- 220
                (220,6),(220,8),(220,10),(220,12),
                -- 250
                (250,8),(250,10),(250,12),(250,14),
                -- 260
                (260,8),(260,10),(260,12)
            ),
            computed AS (
                SELECT
                    b_mm,
                    t_mm,
                    b_mm::text || 'x' || t_mm::text AS desig,
                    b_mm / 10.0                      AS b_cm,
                    (b_mm - 2 * t_mm) / 10.0         AS d_cm
                FROM sizes
            ),
            props AS (
                SELECT
                    b_mm, t_mm, desig, b_cm, d_cm,
                    ROUND((b_cm^2 - d_cm^2)::numeric, 3)                              AS area,
                    ROUND(((b_cm^2 - d_cm^2) * 0.785)::numeric, 3)                   AS mass,
                    ROUND(((b_cm^4 - d_cm^4) / 12.0)::numeric, 2)                    AS inertia,
                    ROUND((SQRT((b_cm^4 - d_cm^4) / 12.0 / (b_cm^2 - d_cm^2)))::numeric, 2) AS r_inertia,
                    ROUND((2.0 * (b_cm^4 - d_cm^4) / 12.0 / b_cm)::numeric, 2)      AS resistance,
                    ROUND(((b_cm^3 - d_cm^3) / 4.0)::numeric, 2)                     AS plastic_w
                FROM computed
            ),
            ins AS (
                INSERT INTO gauge_profile (type_id, name, designation, standard, is_custom)
                SELECT
                    6,
                    'Труба кв. ' || b_mm || '×' || t_mm,
                    desig,
                    'ГОСТ 30245-2003',
                    false
                FROM props
                RETURNING id, designation
            )
            INSERT INTO gauge_pipe_square
                (profile_id, outer_side, wall_thickness,
                 area, mass_per_meter,
                 moment_inertia, radius_inertia,
                 moment_resistance, plastic_moment_resistance)
            SELECT
                ins.id,
                p.b_mm, p.t_mm,
                p.area, p.mass,
                p.inertia, p.r_inertia,
                p.resistance, p.plastic_w
            FROM ins
            JOIN props p ON ins.designation = p.desig
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM gauge_profile
            WHERE type_id = 6 AND standard = 'ГОСТ 30245-2003' AND is_custom = false
        ");
    }
}
