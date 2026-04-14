<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheSeer\Tokenizer\Exception;

final class Version20260412180743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Таблицы сортаментов стальных профилей';
    }

    public function up(Schema $schema): void
    {
        // =====================================================================
        // Справочник типов профилей
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_profile_type (
                id   SERIAL     PRIMARY KEY,
                code VARCHAR(32)  NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL
            );
        ");

        $this->addSql("COMMENT ON COLUMN gauge_profile_type.id   IS 'Идентификатор типа профиля';");
        $this->addSql("COMMENT ON COLUMN gauge_profile_type.code IS 'Код типа: ANGLE_EQUAL, CHANNEL, I_BEAM и т.д.';");
        $this->addSql("COMMENT ON COLUMN gauge_profile_type.name IS 'Наименование типа профиля';");

        // =====================================================================
        $this->addSql("
            INSERT INTO gauge_profile_type (code, name) VALUES
            ('ANGLE_EQUAL',   'Уголок равнополочный'),
            ('ANGLE_UNEQUAL', 'Уголок неравнополочный'),
            ('CHANNEL',       'Швеллер'),
            ('I_BEAM',        'Двутавр'),
            ('PIPE_ROUND',    'Труба круглая'),
            ('PIPE_SQUARE',   'Труба профильная квадратная'),
            ('CIRCLE',        'Круг'),
            ('SHEET',         'Лист');
        ");

        // =====================================================================
        // Основная таблица профилей (общие реквизиты)
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_profile (
                id          BIGSERIAL    PRIMARY KEY,
                type_id     SMALLINT     NOT NULL REFERENCES gauge_profile_type(id),
                name        VARCHAR(255) NOT NULL,
                designation VARCHAR(50)  NOT NULL,
                standard    VARCHAR(255),
                is_custom   BOOLEAN      NOT NULL DEFAULT FALSE,
                created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
                updated_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
                UNIQUE (type_id, designation)
            );");

             $this->addSql("COMMENT ON TABLE  gauge_profile             IS 'Общий каталог стальных профилей';");
             $this->addSql("COMMENT ON COLUMN gauge_profile.name        IS 'Полное наименование, напр. «Уголок 50×50×5»';");
             $this->addSql("COMMENT ON COLUMN gauge_profile.designation IS 'ГОСТовское обозначение, напр. «L50×5»';");
             $this->addSql("COMMENT ON COLUMN gauge_profile.standard    IS 'Нормативный документ, напр. «ГОСТ 8509-93»';");
             $this->addSql("COMMENT ON COLUMN gauge_profile.is_custom   IS 'TRUE — нестандартный (пользовательский) профиль';");

             $this->addSql("CREATE INDEX idx_gauge_profile_type_id ON gauge_profile(type_id);");

        // =====================================================================
        // Уголок равнополочный (ГОСТ 8509-93)
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_angle_equal (
                profile_id                 BIGINT       PRIMARY KEY REFERENCES gauge_profile(id) ON DELETE CASCADE,

                -- Геометрия сечения
                flange_width               NUMERIC(6,2) NOT NULL,  -- ширина полки, мм
                flange_thickness           NUMERIC(5,2) NOT NULL,  -- толщина полки, мм
                inner_fillet_radius        NUMERIC(5,2),           -- радиус внутреннего закругления R, мм
                edge_fillet_radius         NUMERIC(5,2),           -- радиус скругления кромки полки r, мм

                -- Положение центра тяжести
                centroid_distance_x        NUMERIC(5,2) NOT NULL,  -- расстояние от наружной грани до ЦТ по оси x₀, см
                centroid_distance_y        NUMERIC(5,2) NOT NULL,  -- расстояние от наружной грани до ЦТ по оси y₀, см

                -- Площадь и масса
                area                       NUMERIC(8,3) NOT NULL,  -- площадь поперечного сечения, см²
                mass_per_meter             NUMERIC(6,3),           -- теоретическая масса 1 м, кг/м

                -- Моменты инерции
                moment_inertia_x           NUMERIC(10,2) NOT NULL, -- момент инерции относительно оси x, см⁴
                moment_inertia_y           NUMERIC(10,2) NOT NULL, -- момент инерции относительно оси y, см⁴
                moment_inertia_max         NUMERIC(10,2),          -- максимальный главный момент инерции J₁ (ось u), см⁴
                moment_inertia_min         NUMERIC(10,2),          -- минимальный главный момент инерции J₂ (ось v), см⁴
                centrifugal_moment_inertia NUMERIC(10,2),          -- центробежный момент инерции Jxy, см⁴

                -- Радиусы инерции
                radius_inertia_x           NUMERIC(6,2) NOT NULL,  -- радиус инерции относительно оси x, см
                radius_inertia_y           NUMERIC(6,2) NOT NULL,  -- радиус инерции относительно оси y, см
                radius_inertia_min         NUMERIC(6,2),           -- минимальный радиус инерции i_min (ось v), см

                -- Моменты сопротивления
                moment_resistance_x        NUMERIC(8,2) NOT NULL,  -- момент сопротивления Wx, см³
                moment_resistance_y        NUMERIC(8,2) NOT NULL   -- момент сопротивления Wy, см³
            );");

            $this->addSql("COMMENT ON TABLE  gauge_angle_equal                           IS 'Сортамент уголков равнополочных (ГОСТ 8509-93)';");
            $this->addSql("COMMENT ON COLUMN gauge_angle_equal.moment_inertia_max        IS 'Главный максимальный момент инерции J₁ относительно оси u (под 45° к x/y), см⁴';");
            $this->addSql("COMMENT ON COLUMN gauge_angle_equal.moment_inertia_min        IS 'Главный минимальный момент инерции J₂ относительно оси v (под 45° к x/y), см⁴; определяет устойчивость';");
            $this->addSql("COMMENT ON COLUMN gauge_angle_equal.centrifugal_moment_inertia IS 'Центробежный момент инерции Jxy; для равнополочного уголка отрицателен';");
            $this->addSql("COMMENT ON COLUMN gauge_angle_equal.radius_inertia_min        IS 'Минимальный радиус инерции i_min = √(J_min / A); критичен при расчёте на устойчивость';");

        // =====================================================================
        // Швеллер (ГОСТ 8240-97)
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_channel (
                profile_id               BIGINT        PRIMARY KEY REFERENCES gauge_profile(id) ON DELETE CASCADE,

                -- Геометрия сечения
                height                   NUMERIC(6,2)  NOT NULL,  -- высота профиля h, мм
                flange_width             NUMERIC(6,2)  NOT NULL,  -- ширина полки b, мм
                web_thickness            NUMERIC(5,2)  NOT NULL,  -- толщина стенки s, мм
                flange_thickness         NUMERIC(5,2)  NOT NULL,  -- средняя толщина полки t, мм
                inner_fillet_radius      NUMERIC(5,2),            -- радиус внутреннего закругления R, мм
                edge_fillet_radius       NUMERIC(5,2),            -- радиус скругления кромки полки r, мм

                -- Положение центра тяжести
                centroid_distance_z      NUMERIC(5,2)  NOT NULL,  -- расстояние от наружной грани стенки до ЦТ z₀, см

                -- Площадь и масса
                area                     NUMERIC(8,3)  NOT NULL,  -- площадь поперечного сечения, см²
                mass_per_meter           NUMERIC(6,3),            -- теоретическая масса 1 м, кг/м

                -- Моменты инерции
                moment_inertia_x         NUMERIC(10,2) NOT NULL,  -- момент инерции относительно оси x, см⁴
                moment_inertia_y         NUMERIC(10,2) NOT NULL,  -- момент инерции относительно оси y, см⁴

                -- Радиусы инерции
                radius_inertia_x         NUMERIC(6,2)  NOT NULL,  -- радиус инерции относительно оси x, см
                radius_inertia_y         NUMERIC(6,2)  NOT NULL,  -- радиус инерции относительно оси y, см

                -- Моменты сопротивления
                moment_resistance_x      NUMERIC(8,2)  NOT NULL,  -- момент сопротивления Wx, см³
                moment_resistance_y      NUMERIC(8,2)  NOT NULL,  -- момент сопротивления Wy (к дальней от ЦТ грани полки), см³
                moment_resistance_y_near NUMERIC(8,2),            -- момент сопротивления W'y (к грани стенки, ближней к ЦТ), см³

                -- Статический момент
                static_moment_x          NUMERIC(8,2)  NOT NULL   -- статический момент полусечения относительно оси x Sx, см³
            ); ");

            $this->addSql("COMMENT ON TABLE  gauge_channel                        IS 'Сортамент швеллеров (ГОСТ 8240-97)'; ");
            $this->addSql("COMMENT ON COLUMN gauge_channel.centroid_distance_z    IS 'Расстояние от наружной грани стенки до центра тяжести z₀, см; сечение несимметрично относительно оси y'; ");
            $this->addSql("COMMENT ON COLUMN gauge_channel.moment_resistance_y    IS 'Wy = Iy / (b − z₀) — к дальней от ЦТ грани полки (меньшее значение, определяющее прочность)'; ");
            $this->addSql("COMMENT ON COLUMN gauge_channel.moment_resistance_y_near IS 'W''y = Iy / z₀ — к ближней грани (стенка); большее значение, можно вычислить, но удобно хранить'; ");
            $this->addSql("COMMENT ON COLUMN gauge_channel.static_moment_x        IS 'Статический момент полусечения Sx, см³; используется при расчёте касательных напряжений'; ");

        // =====================================================================
        // Двутавр (ГОСТ 8239-89)
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_i_beam (
                profile_id               BIGINT        PRIMARY KEY REFERENCES gauge_profile(id) ON DELETE CASCADE,

                -- Геометрия сечения
                height                   NUMERIC(6,2)  NOT NULL,  -- высота профиля h, мм
                flange_width             NUMERIC(6,2)  NOT NULL,  -- ширина полки b, мм
                web_thickness            NUMERIC(5,2)  NOT NULL,  -- толщина стенки s, мм
                flange_thickness         NUMERIC(5,2)  NOT NULL,  -- толщина полки t, мм
                inner_fillet_radius      NUMERIC(5,2),            -- радиус внутреннего закругления R, мм
                edge_fillet_radius       NUMERIC(5,2),            -- радиус скругления кромки полки r, мм

                -- Площадь и масса
                area                     NUMERIC(8,3)  NOT NULL,  -- площадь поперечного сечения, см²
                mass_per_meter           NUMERIC(6,3),            -- теоретическая масса 1 м, кг/м

                -- Моменты инерции
                moment_inertia_x         NUMERIC(12,2) NOT NULL,  -- момент инерции относительно оси x (сильная ось), см⁴
                moment_inertia_y         NUMERIC(10,2) NOT NULL,  -- момент инерции относительно оси y (слабая ось), см⁴

                -- Радиусы инерции
                radius_inertia_x         NUMERIC(6,2)  NOT NULL,  -- радиус инерции относительно оси x, см
                radius_inertia_y         NUMERIC(6,2)  NOT NULL,  -- радиус инерции относительно оси y, см

                -- Моменты сопротивления
                moment_resistance_x      NUMERIC(10,2) NOT NULL,  -- момент сопротивления Wx, см³
                moment_resistance_y      NUMERIC(8,2)  NOT NULL,  -- момент сопротивления Wy, см³

                -- Статический момент
                static_moment_x          NUMERIC(10,2) NOT NULL   -- статический момент полусечения относительно оси x Sx, см³
            ); ");

            $this->addSql("COMMENT ON TABLE  gauge_i_beam IS 'Сортамент двутавров (ГОСТ 8239-89)'; ");
            $this->addSql("COMMENT ON COLUMN gauge_i_beam.static_moment_x IS 'Статический момент полусечения Sx, см³; используется при расчёте касательных напряжений в стенке'; ");

        // =====================================================================
        // Труба круглая (ГОСТ 8734-75 / ГОСТ 10704-91)
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_pipe_round (
                profile_id                BIGINT        PRIMARY KEY REFERENCES gauge_profile(id) ON DELETE CASCADE,

                -- Геометрия сечения
                outer_diameter            NUMERIC(7,2)  NOT NULL,  -- наружный диаметр D, мм
                wall_thickness            NUMERIC(5,2)  NOT NULL,  -- толщина стенки t, мм

                -- Площадь и масса
                area                      NUMERIC(8,3)  NOT NULL,  -- площадь поперечного сечения, см²
                mass_per_meter            NUMERIC(6,3),            -- теоретическая масса 1 м, кг/м

                -- Моменты инерции и радиус (ось x = ось y для круглого сечения)
                moment_inertia            NUMERIC(10,2) NOT NULL,  -- осевой момент инерции Ix = Iy, см⁴
                radius_inertia            NUMERIC(6,2)  NOT NULL,  -- радиус инерции i, см

                -- Моменты сопротивления
                moment_resistance         NUMERIC(8,2)  NOT NULL,  -- осевой момент сопротивления Wx = Wy, см³
                plastic_moment_resistance NUMERIC(8,2)             -- пластический момент сопротивления Wpl, см³
            );");

            $this->addSql("COMMENT ON TABLE  gauge_pipe_round                       IS 'Сортамент труб электросварных круглых (ГОСТ 10704-91) и холоднодеформированных (ГОСТ 8734-75)';");
            $this->addSql("COMMENT ON COLUMN gauge_pipe_round.plastic_moment_resistance IS 'Пластический момент сопротивления Wpl; применяется при расчёте по предельным состояниям второго рода';");

        // =====================================================================
        // Труба профильная квадратная (ГОСТ 30245-2003)
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_pipe_square (
                profile_id                BIGINT        PRIMARY KEY REFERENCES gauge_profile(id) ON DELETE CASCADE,

                -- Геометрия сечения
                outer_side                NUMERIC(6,2)  NOT NULL,  -- наружный размер стороны квадрата B, мм
                wall_thickness            NUMERIC(5,2)  NOT NULL,  -- толщина стенки t, мм

                -- Площадь и масса
                area                      NUMERIC(8,3)  NOT NULL,  -- площадь поперечного сечения, см²
                mass_per_meter            NUMERIC(6,3),            -- теоретическая масса 1 м, кг/м

                -- Моменты инерции и радиус (Ix = Iy для квадратного сечения)
                moment_inertia            NUMERIC(10,2) NOT NULL,  -- осевой момент инерции Ix = Iy, см⁴
                radius_inertia            NUMERIC(6,2)  NOT NULL,  -- радиус инерции i, см

                -- Моменты сопротивления
                moment_resistance         NUMERIC(8,2)  NOT NULL,  -- осевой момент сопротивления Wx = Wy, см³
                plastic_moment_resistance NUMERIC(8,2)             -- пластический момент сопротивления Wpl, см³
            );");

            $this->addSql("COMMENT ON TABLE  gauge_pipe_square IS 'Сортамент труб профильных квадратных (ГОСТ 30245-2003)';");

        // =====================================================================
        // Круг (сплошной круглый прокат, ГОСТ 2590-2006)
        // =====================================================================
        $this->addSql("
            CREATE TABLE IF NOT EXISTS gauge_round_solid (
                profile_id                  BIGINT        PRIMARY KEY REFERENCES gauge_profile(id) ON DELETE CASCADE,

                -- Геометрия сечения
                diameter                    NUMERIC(7,2)  NOT NULL,  -- диаметр d, мм

                -- Площадь и масса
                area                        NUMERIC(8,3)  NOT NULL,  -- площадь поперечного сечения, см²
                mass_per_meter              NUMERIC(6,3),            -- теоретическая масса 1 м, кг/м

                -- Осевые характеристики (Ix = Iy для круга)
                moment_inertia              NUMERIC(10,2) NOT NULL,  -- осевой момент инерции Ix = Iy, см⁴
                radius_inertia              NUMERIC(6,2)  NOT NULL,  -- радиус инерции i, см
                moment_resistance           NUMERIC(8,2)  NOT NULL,  -- осевой момент сопротивления Wx = Wy, см³

                -- Полярные и пластические характеристики (опционально)
                polar_moment_inertia        NUMERIC(10,2),           -- полярный момент инерции Ip = 2·Ix, см⁴
                polar_moment_resistance     NUMERIC(8,2),            -- полярный момент сопротивления Wp = Ip / (d/2), см³
                plastic_moment_resistance   NUMERIC(8,2)             -- пластический момент сопротивления Wpl, см³
            );");

            $this->addSql("COMMENT ON TABLE  gauge_round_solid                         IS 'Сортамент круглого проката (сплошной круг, ГОСТ 2590-2006)';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.diameter                IS 'Диаметр d, мм';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.area                    IS 'Площадь поперечного сечения A = π·d²/4, см²';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.moment_inertia          IS 'Осевой момент инерции Ix = Iy = π·d⁴/64, см⁴';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.radius_inertia          IS 'Радиус инерции i = d/4, см';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.moment_resistance       IS 'Осевой момент сопротивления Wx = Wy = π·d³/32, см³';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.polar_moment_inertia    IS 'Полярный момент инерции Ip = π·d⁴/32 = 2·Ix, см⁴';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.polar_moment_resistance IS 'Полярный момент сопротивления Wp = π·d³/16, см³';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.plastic_moment_resistance IS 'Пластический момент сопротивления Wpl = d³/6, см³; применяется при расчёте по СП 16.13330';");
            $this->addSql("COMMENT ON COLUMN gauge_round_solid.mass_per_meter          IS 'Теоретическая масса 1 м, кг/м';");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS gauge_round_solid');
        $this->addSql('DROP TABLE IF EXISTS gauge_pipe_square');
        $this->addSql('DROP TABLE IF EXISTS gauge_pipe_round');
        $this->addSql('DROP TABLE IF EXISTS gauge_i_beam');
        $this->addSql('DROP TABLE IF EXISTS gauge_channel');
        $this->addSql('DROP TABLE IF EXISTS gauge_angle_equal');
        $this->addSql('DROP TABLE IF EXISTS gauge_profile');
        $this->addSql('DROP TABLE IF EXISTS gauge_profile_type');
    }
}
