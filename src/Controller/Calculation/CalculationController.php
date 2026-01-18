<?php

declare(strict_types=1);

namespace App\Controller\Calculation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CalculationController extends AbstractController
{
    #[Route('/calculation/concrete-pillar', name: 'app_concrete_pillar_calc')]
    #[IsGranted('ROLE_USER')]
    public function concretePillar(): Response
    {
        return $this->render('calculation/concrete_pillar.html.twig', [
            'page_title' => 'Расчет ЖБ столба на ветровую нагрузку',
        ]);
    }

    #[Route('/api/calculate/concrete-pillar', name: 'api_concrete_pillar_calc', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function calculateConcretePillar(): Response
    {
        // Здесь будет обработка AJAX запросов для расчета
        return $this->json(['status' => 'success', 'message' => 'Calculation endpoint']);
    }
}
