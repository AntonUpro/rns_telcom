<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Entity\Calculation;
use App\Enum\CalculationStatusEnum;
use App\Enum\CalculationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        // Перенаправляем на логин, если не авторизован
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Обработка создания нового расчета
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $type = $request->request->get('type');

            if ($name && $type && CalculationTypeEnum::tryFrom($type)) {
                $calculation = new Calculation();
                $calculation->setUser($user);
                $calculation->setName($name);
                $calculation->setType(CalculationTypeEnum::from($type));
                $calculation->setStatus(CalculationStatusEnum::DRAFT);

                $this->entityManager->persist($calculation);
                $this->entityManager->flush();

                $this->addFlash('success', 'Расчет успешно создан');

                return $this->redirectToRoute('app_concrete_pillar_calc', ['calculationId' => $calculation->getId()]);
            } else {
                $this->addFlash('error', 'Заполните все обязательные поля');
            }
        }

        // Получаем расчеты пользователя
        $calculations = $this->entityManager->getRepository(Calculation::class)
            ->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC']
            );

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'calculations' => $calculations,
            'calculation_types' => CalculationTypeEnum::cases(),
        ]);
    }

    #[Route('/calculation/{id}/change-status', name: 'app_calculation_change_status', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function changeStatus(
        Calculation $calculation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Проверяем, что расчет принадлежит текущему пользователю
        if ($calculation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('У вас нет доступа к этому расчету');
        }

        $statusValue = $request->request->get('status');

        if ($statusValue && CalculationStatusEnum::tryFrom($statusValue)) {
            $calculation->setStatus(CalculationStatusEnum::from($statusValue));
            $entityManager->flush();

            $this->addFlash('success', 'Статус расчета обновлен');
        } else {
            $this->addFlash('error', 'Неверный статус');
        }

        return $this->redirectToRoute('app_dashboard');
    }
}
