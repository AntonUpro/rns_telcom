<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class DefaultConstant
{
    public const SECURITY_COEFFICIENT = 1.4;

    public const CABLE_CANAL_CX_COEFFICIENT = 1.4;
    public const LADDER_CX_COEFFICIENT = 1.4;
    public const CABLE_CX_COEFFICIENT = 1.2;

    public const CABLE_RRL_DIAMETER = 10;
    public const CABLE_RADIO_OPTIC_DIAMETER = 7;
    public const CABLE_RADIO_POWER_DIAMETER = 17;
    public const CABLE_OTHER_EQUIPMENT_DIAMETER = 0;

    public const CONSTRUCTION_VALUE_CABLE_TRAY = 0.04;
    public const CONSTRUCTION_VALUE_LADDER = 0.12;
    public const CONSTRUCTION_VALUE_CABLE_TRAY_BOTTOM = 3;
    public const CONSTRUCTION_VALUE_LADDER_BOTTOM = 3;

    public const SHADING_COEFFICIENT_RRL = 1;
    public const SHADING_COEFFICIENT_PANEL_ANTENNA = 0.8;
    public const SHADING_COEFFICIENT_RADIO_BLOCKS = 0.3;
    public const SHADING_COEFFICIENT_CABLE_TRAY = 0.3;
    public const SHADING_COEFFICIENT_OTHER_EQUIPMENT = 1;

}
