<?php

declare(strict_types=1);

namespace App\Service\Gauge;

use App\Entity\Gauge\GaugeAngleEqual;
use App\Entity\Gauge\GaugeChannel;
use App\Entity\Gauge\GaugeIBeam;
use App\Entity\Gauge\GaugePipeRound;
use App\Entity\Gauge\GaugePipeSquare;
use App\Entity\Gauge\GaugeRoundSolid;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Repository\Gauge\GaugeAngleEqualRepository;
use App\Repository\Gauge\GaugeChannelRepository;
use App\Repository\Gauge\GaugeIBeamRepository;
use App\Repository\Gauge\GaugePipeRoundRepository;
use App\Repository\Gauge\GaugePipeSquareRepository;
use App\Repository\Gauge\GaugeRoundSolidRepository;

class GaugeSearchService
{
    public function __construct(
        private readonly GaugeAngleEqualRepository $angleEqualRepo,
        private readonly GaugeChannelRepository $channelRepo,
        private readonly GaugeIBeamRepository $iBeamRepo,
        private readonly GaugePipeRoundRepository $pipeRoundRepo,
        private readonly GaugePipeSquareRepository $pipeSquareRepo,
        private readonly GaugeRoundSolidRepository $roundSolidRepo,
    ) {
    }

    /**
     * Поиск профилей по типу и подстроке (в designation или name).
     *
     * Структура каждого элемента ответа:
     * {
     *   id, designation, name, standard, area,
     *   momentResistances: [{ key, label, value }, ...],
     *   defaultMomentResistanceKey: string
     * }
     *
     * Для типов без таблицы в БД (ANGLE_UNEQUAL, SHEET) возвращается пустой массив.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(GaugeProfileTypeEnum $type, string $query, int $limit = 15): array
    {
        return match ($type) {
            GaugeProfileTypeEnum::ANGLE_EQUAL => array_map(
                $this->serializeAngleEqual(...),
                $this->angleEqualRepo->searchByQuery($query, $limit),
            ),
            GaugeProfileTypeEnum::CHANNEL => array_map(
                $this->serializeChannel(...),
                $this->channelRepo->searchByQuery($query, $limit),
            ),
            GaugeProfileTypeEnum::I_BEAM => array_map(
                $this->serializeIBeam(...),
                $this->iBeamRepo->searchByQuery($query, $limit),
            ),
            GaugeProfileTypeEnum::PIPE_ROUND => array_map(
                $this->serializePipeRound(...),
                $this->pipeRoundRepo->searchByQuery($query, $limit),
            ),
            GaugeProfileTypeEnum::PIPE_SQUARE => array_map(
                $this->serializePipeSquare(...),
                $this->pipeSquareRepo->searchByQuery($query, $limit),
            ),
            GaugeProfileTypeEnum::CIRCLE => array_map(
                $this->serializeRoundSolid(...),
                $this->roundSolidRepo->searchByQuery($query, $limit),
            ),
            default => [],
        };
    }

    private function profileBase(object $gauge): array
    {
        $p = $gauge->getProfile();

        return [
            'id' => $p->getId(),
            'designation' => $p->getDesignation(),
            'name' => $p->getName(),
            'standard' => $p->getStandard(),
        ];
    }

    private function serializeAngleEqual(GaugeAngleEqual $g): array
    {
        // Равнополочный уголок: Wx = Wy — берём одно значение
        return [
            ...$this->profileBase($g),
            'area' => (float)$g->getArea(),
            'momentResistances' => [
                ['key' => 'x', 'label' => 'Wx/Wy', 'value' => (float)$g->getMomentResistanceX()],
            ],
            'defaultMomentResistanceKey' => 'x',
        ];
    }

    private function serializeChannel(GaugeChannel $g): array
    {
        $wx = (float)$g->getMomentResistanceX();
        $wy = (float)$g->getMomentResistanceY();       // к дальней грани полки — минимум
        $wyNear = (float)($g->getMomentResistanceYNear() ?? 0.0); // к ближней грани стенки

        return [
            ...$this->profileBase($g),
            'area' => (float)$g->getArea(),
            'momentResistances' => [
                ['key' => 'x', 'label' => 'Wx (ось X)', 'value' => $wx],
                ['key' => 'y', 'label' => 'Wy (дальний)', 'value' => $wy],
                ['key' => 'y_near', 'label' => "W'y (ближний)", 'value' => $wyNear],
            ],
            'defaultMomentResistanceKey' => 'y',
        ];
    }

    private function serializeIBeam(GaugeIBeam $g): array
    {
        return [
            ...$this->profileBase($g),
            'area' => (float)$g->getArea(),
            'momentResistances' => [
                ['key' => 'x', 'label' => 'Wx (ось X)', 'value' => (float)$g->getMomentResistanceX()],
                ['key' => 'y', 'label' => 'Wy (ось Y)', 'value' => (float)$g->getMomentResistanceY()],
            ],
            'defaultMomentResistanceKey' => 'y',
        ];
    }

    private function serializePipeRound(GaugePipeRound $g): array
    {
        return [
            ...$this->profileBase($g),
            'area' => (float)$g->getArea(),
            'momentResistances' => [
                ['key' => 'w', 'label' => 'W', 'value' => (float)$g->getMomentResistance()],
            ],
            'defaultMomentResistanceKey' => 'w',
        ];
    }

    private function serializePipeSquare(GaugePipeSquare $g): array
    {
        return [
            ...$this->profileBase($g),
            'area' => (float)$g->getArea(),
            'momentResistances' => [
                ['key' => 'w', 'label' => 'W', 'value' => (float)$g->getMomentResistance()],
            ],
            'defaultMomentResistanceKey' => 'w',
        ];
    }

    private function serializeRoundSolid(GaugeRoundSolid $g): array
    {
        return [
            ...$this->profileBase($g),
            'area' => (float)$g->getArea(),
            'momentResistances' => [
                ['key' => 'w', 'label' => 'W', 'value' => (float)$g->getMomentResistance()],
            ],
            'defaultMomentResistanceKey' => 'w',
        ];
    }
}
