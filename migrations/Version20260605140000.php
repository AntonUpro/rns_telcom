<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Трубы электросварные круглые ГОСТ 10704-91.
 * Диапазон: D=10–820 мм, практические толщины стенки.
 *
 * Обозначение: «DxT» (мм), напр. «57x3.5».
 *
 * Формулы (D, t в мм; D_cm = D/10, d_cm = (D−2t)/10):
 *   A   = π·(D_cm²−d_cm²)/4                                     см²
 *   m   = A·0.785                                                кг/м
 *   I   = π·(D_cm⁴−d_cm⁴)/64                                    см⁴
 *   i   = √((D_cm²+d_cm²)/16)                                   см
 *   W   = π·(D_cm⁴−d_cm⁴)/(32·D_cm)                            см³
 *   Wpl = (D_cm³−d_cm³)/6                                        см³
 */
final class Version20260605140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Сортамент труб круглых сварных ГОСТ 10704-91 (D=10–820 мм)';
    }

    public function up(Schema $schema): void
    {
        // type_id=5 → PIPE_ROUND
        // Пары (d_mm, t_mm·10) — толщина хранится как целое число ×10 для точности
        // чтобы избежать float в VALUES; потом делим на 10.
        $this->addSql("
            WITH sizes(d_mm, t10) AS (
                VALUES
                -- D=10
                (10,12),(10,15),(10,20),
                -- D=12
                (12,15),(12,20),
                -- D=14
                (14,15),(14,20),
                -- D=16
                (16,15),(16,20),
                -- D=18
                (18,20),(18,25),
                -- D=20
                (20,20),(20,25),(20,30),
                -- D=22
                (22,20),(22,25),(22,30),
                -- D=25
                (25,20),(25,25),(25,30),(25,40),
                -- D=28
                (28,20),(28,25),(28,30),(28,40),
                -- D=32
                (32,20),(32,25),(32,30),(32,40),
                -- D=38
                (38,20),(38,25),(38,30),(38,40),
                -- D=42
                (42,25),(42,30),(42,35),(42,40),
                -- D=45
                (45,25),(45,30),(45,35),(45,40),(45,50),
                -- D=50
                (50,25),(50,30),(50,35),(50,40),(50,50),
                -- D=51
                (51,25),(51,30),(51,35),(51,40),
                -- D=57
                (57,25),(57,30),(57,35),(57,40),(57,50),(57,60),
                -- D=60
                (60,30),(60,35),(60,40),(60,50),(60,60),
                -- D=63.5 — нецелый, обрабатываем отдельно ниже
                -- D=70
                (70,30),(70,40),(70,50),(70,60),(70,80),
                -- D=76
                (76,30),(76,35),(76,40),(76,50),(76,60),(76,80),
                -- D=83
                (83,35),(83,40),(83,50),(83,60),
                -- D=89
                (89,30),(89,35),(89,40),(89,50),(89,60),(89,80),
                -- D=102
                (102,40),(102,50),(102,60),(102,80),
                -- D=108
                (108,40),(108,50),(108,60),(108,80),(108,100),
                -- D=114
                (114,40),(114,50),(114,60),(114,80),(114,100),
                -- D=121
                (121,40),(121,50),(121,60),(121,80),
                -- D=127
                (127,40),(127,50),(127,60),(127,80),(127,100),
                -- D=133
                (133,40),(133,50),(133,60),(133,80),(133,100),(133,120),
                -- D=140
                (140,40),(140,50),(140,60),(140,80),(140,100),(140,120),
                -- D=146
                (146,50),(146,60),(146,80),(146,100),
                -- D=152
                (152,50),(152,60),(152,80),(152,100),(152,120),
                -- D=159
                (159,50),(159,60),(159,80),(159,100),(159,120),
                -- D=168
                (168,50),(168,60),(168,80),(168,100),(168,120),
                -- D=177
                (177,60),(177,80),(177,100),(177,120),
                -- D=194
                (194,60),(194,80),(194,100),(194,120),
                -- D=203
                (203,60),(203,80),(203,100),(203,120),
                -- D=219
                (219,60),(219,80),(219,100),(219,120),(219,140),
                -- D=245
                (245,60),(245,80),(245,100),(245,120),
                -- D=273
                (273,70),(273,80),(273,100),(273,120),(273,140),
                -- D=325
                (325,80),(325,90),(325,100),(325,120),(325,140),(325,160),
                -- D=377
                (377,90),(377,100),(377,120),(377,140),
                -- D=426
                (426,80),(426,100),(426,120),(426,140),(426,160),
                -- D=480
                (480,100),(480,120),(480,140),
                -- D=530
                (530,80),(530,100),(530,120),(530,140),(530,160),
                -- D=630
                (630,80),(630,100),(630,120),(630,140),
                -- D=720
                (720,80),(720,100),(720,120),
                -- D=820
                (820,80),(820,100),(820,120)
            ),
            params AS (
                SELECT
                    d_mm,
                    t10 / 10.0                              AS t_mm,
                    d_mm::text || 'x' || (t10/10.0)::text  AS desig,
                    d_mm / 10.0                             AS do_cm,
                    (d_mm - 2.0 * t10 / 10.0) / 10.0       AS di_cm
                FROM sizes
                WHERE d_mm > 2.0 * t10 / 10.0  -- исключить нефизичные пары
            ),
            props AS (
                SELECT
                    d_mm, t_mm, desig, do_cm, di_cm,
                    ROUND((PI() * (do_cm^2 - di_cm^2) / 4.0)::numeric, 3)                      AS area,
                    ROUND((PI() * (do_cm^2 - di_cm^2) / 4.0 * 0.785)::numeric, 3)              AS mass,
                    ROUND((PI() * (do_cm^4 - di_cm^4) / 64.0)::numeric, 2)                     AS inertia,
                    ROUND((SQRT((do_cm^2 + di_cm^2) / 16.0))::numeric, 2)                      AS r_inertia,
                    ROUND((PI() * (do_cm^4 - di_cm^4) / (32.0 * do_cm))::numeric, 2)           AS resistance,
                    ROUND(((do_cm^3 - di_cm^3) / 6.0)::numeric, 2)                             AS plastic_w
                FROM params
            ),
            ins AS (
                INSERT INTO gauge_profile (type_id, name, designation, standard, is_custom)
                SELECT
                    5,
                    'Труба ' || d_mm || '×' || t_mm,
                    desig,
                    'ГОСТ 10704-91',
                    false
                FROM props
                ORDER BY d_mm, t_mm
                RETURNING id, designation
            )
            INSERT INTO gauge_pipe_round
                (profile_id, outer_diameter, wall_thickness,
                 area, mass_per_meter,
                 moment_inertia, radius_inertia,
                 moment_resistance, plastic_moment_resistance)
            SELECT
                ins.id,
                p.d_mm, p.t_mm,
                p.area, p.mass,
                p.inertia, p.r_inertia,
                p.resistance, p.plastic_w
            FROM ins
            JOIN props p ON ins.designation = p.desig
        ");

        // D=63.5 вставляем отдельно (нецелый диаметр)
        $this->addSql("
            WITH sizes(d_mm, t_mm) AS (
                VALUES
                (63.5, 3.0),(63.5, 4.0),(63.5, 5.0)
            ),
            props AS (
                SELECT
                    d_mm, t_mm,
                    d_mm::text || 'x' || t_mm::text   AS desig,
                    d_mm / 10.0                         AS do_cm,
                    (d_mm - 2.0 * t_mm) / 10.0         AS di_cm
                FROM sizes
            ),
            ext AS (
                SELECT
                    d_mm, t_mm, desig, do_cm, di_cm,
                    ROUND((PI() * (do_cm^2 - di_cm^2) / 4.0)::numeric, 3)                      AS area,
                    ROUND((PI() * (do_cm^2 - di_cm^2) / 4.0 * 0.785)::numeric, 3)              AS mass,
                    ROUND((PI() * (do_cm^4 - di_cm^4) / 64.0)::numeric, 2)                     AS inertia,
                    ROUND((SQRT((do_cm^2 + di_cm^2) / 16.0))::numeric, 2)                      AS r_inertia,
                    ROUND((PI() * (do_cm^4 - di_cm^4) / (32.0 * do_cm))::numeric, 2)           AS resistance,
                    ROUND(((do_cm^3 - di_cm^3) / 6.0)::numeric, 2)                             AS plastic_w
                FROM props
            ),
            ins AS (
                INSERT INTO gauge_profile (type_id, name, designation, standard, is_custom)
                SELECT 5, 'Труба ' || d_mm || '×' || t_mm, desig, 'ГОСТ 10704-91', false
                FROM ext
                RETURNING id, designation
            )
            INSERT INTO gauge_pipe_round
                (profile_id, outer_diameter, wall_thickness,
                 area, mass_per_meter,
                 moment_inertia, radius_inertia,
                 moment_resistance, plastic_moment_resistance)
            SELECT ins.id, e.d_mm, e.t_mm, e.area, e.mass, e.inertia, e.r_inertia, e.resistance, e.plastic_w
            FROM ins JOIN ext e ON ins.designation = e.desig
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM gauge_profile
            WHERE type_id = 5 AND standard = 'ГОСТ 10704-91' AND is_custom = false
        ");
    }
}
