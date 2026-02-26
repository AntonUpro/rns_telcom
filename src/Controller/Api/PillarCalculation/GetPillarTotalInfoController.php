<?php

declare(strict_types=1);

namespace App\Controller\Api\PillarCalculation;

use App\Controller\Api\AbstractApiController;
use App\Dto\DefaultConstant;
use App\Enum\CalculationData\IcingRegionEnum;
use App\Enum\CalculationData\SnowRegionEnum;
use App\Enum\CalculationData\TerrainTypeEnum;
use App\Enum\CalculationData\WindRegionEnum;
use App\Enum\Pillar\PillarEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class GetPillarTotalInfoController extends AbstractApiController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/information/total-info-pillar', name: 'api_total_info_pillar', methods: ['GET'])]
    public function saveGeneralData(Request $request): JsonResponse
    {
        try {
            return $this->successResponse([
                'windRegions' => WindRegionEnum::choices(),
                'terrainTypes' => TerrainTypeEnum::choices(),
                'icingRegions' => IcingRegionEnum::choices(),
                'snowRegions' => SnowRegionEnum::choices(),
                'pillarTypes' => PillarEnum::choices(),
                'defaultValues' => [
                    'cableDiameterValues' => [
                        'rrl' => DefaultConstant::CABLE_RRL_DIAMETER,
                        'optical' => DefaultConstant::CABLE_RADIO_OPTIC_DIAMETER,
                        'power' => DefaultConstant::CABLE_RADIO_POWER_DIAMETER,
                        'otherEquipment' => DefaultConstant::CABLE_OTHER_EQUIPMENT_DIAMETER,
                    ],
                    'constructionValues' => [
                        'cableTray' => DefaultConstant::CONSTRUCTION_VALUE_CABLE_TRAY,
                        'ladder' => DefaultConstant::CONSTRUCTION_VALUE_LADDER,
                        'cableTrayBottom' => DefaultConstant::CONSTRUCTION_VALUE_CABLE_TRAY_BOTTOM,
                        'ladderBottom' => DefaultConstant::CONSTRUCTION_VALUE_LADDER_BOTTOM,
                    ],
                    'shadingCoefficients' => [
                        'rrl' => DefaultConstant::SHADING_COEFFICIENT_RRL,
                        'panelAntenna' => DefaultConstant::SHADING_COEFFICIENT_PANEL_ANTENNA,
                        'radioBlocks' => DefaultConstant::SHADING_COEFFICIENT_RADIO_BLOCKS,
                        'cableTray' => DefaultConstant::SHADING_COEFFICIENT_CABLE_TRAY,
                        'otherEquipment' => DefaultConstant::SHADING_COEFFICIENT_OTHER_EQUIPMENT,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Ошибка сохранения данных: %s', $e->getMessage()),
                ['trace' => $e->getTraceAsString()]
            );
            return $this->errorResponse($e->getMessage());
        }
    }
}
