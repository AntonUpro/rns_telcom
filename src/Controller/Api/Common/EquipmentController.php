<?php

declare(strict_types=1);

namespace App\Controller\Api\Common;

use App\Controller\Api\AbstractApiController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class EquipmentController extends AbstractApiController
{
    #[Route('/equipment/search', name: 'api_equipment_search', methods: ['POST'])]
    public function index()
    {
        $equipment = [
            [
                'id' => 1,
                'brand' => 'Huawei',
                'model' => 'V1q124124',
                'type' => 'Панельная',
                'width' => 1500,
                'height' => 1800,
                'depth' => 150,
                'weight' => 15.6,
            ],
            [
                'id' => 2,
                'brand' => 'Huawei',
                'model' => 'V1q1241',
                'type' => 'Панельная',
                'width' => 1800,
                'height' => 1800,
                'depth' => 150,
                'weight' => 15.6,
            ],
            [
                'id' => 3,
                'brand' => 'Huawei',
                'model' => 'V1q124',
                'type' => 'Панельная',
                'width' => 2000,
                'height' => 1500,
                'depth' => 150,
                'weight' => 15.6,
            ],
        ];

        return $this->successResponse(
            $equipment
        );
    }
}
