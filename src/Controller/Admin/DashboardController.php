<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Gauge\GaugeAngleEqualCrudController;
use App\Controller\Admin\Gauge\GaugeChannelCrudController;
use App\Controller\Admin\Gauge\GaugeIBeamCrudController;
use App\Controller\Admin\Gauge\GaugePipeRoundCrudController;
use App\Controller\Admin\Gauge\GaugePipeSquareCrudController;
use App\Controller\Admin\Gauge\GaugeRoundSolidCrudController;
use App\Entity\Calculation;
use App\Entity\Customer;
use App\Entity\Equipment;
use App\Entity\Gauge\GaugeAngleEqual;
use App\Entity\Gauge\GaugeChannel;
use App\Entity\Gauge\GaugeIBeam;
use App\Entity\Gauge\GaugePipeRound;
use App\Entity\Gauge\GaugePipeSquare;
use App\Entity\Gauge\GaugeRoundSolid;
use App\Entity\AppendixStaticImage;
use App\Entity\Operator;
use App\Entity\User;
use App\Repository\CalculationRepository;
use App\Repository\EquipmentRepository;
use App\Repository\OperatorRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CalculationRepository $calculationRepository,
        private readonly EquipmentRepository $equipmentRepository,
        private readonly OperatorRepository $operatorRepository,
    ) {
    }

    public function index(): Response
    {
        $totalUsers         = $this->userRepository->count([]);
        $totalCalculations  = $this->calculationRepository->count([]);
        $totalEquipment     = $this->equipmentRepository->count([]);
        $totalOperators     = $this->operatorRepository->count([]);

        $recentCalculations = $this->calculationRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            10
        );

        $userStats = $this->calculationRepository->getUserCalculationStats();

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers'         => $totalUsers,
            'totalCalculations'  => $totalCalculations,
            'totalEquipment'     => $totalEquipment,
            'totalOperators'     => $totalOperators,
            'recentCalculations' => $recentCalculations,
            'userStats'          => $userStats,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('RNS Telcom — Администрирование')
            ->setFaviconPath('favicon.ico')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Главная', 'fa fa-home');
        yield MenuItem::linkToRoute('На сайт', 'fa fa-globe', 'app_dashboard');

        yield MenuItem::section('Пользователи');
        yield MenuItem::linkToCrud('Пользователи', 'fa fa-users', User::class);

        yield MenuItem::section('Расчёты');
        yield MenuItem::linkToCrud('Все расчёты', 'fa fa-calculator', Calculation::class);
        yield MenuItem::linkToCrud('Приложения 5–7', 'fa fa-images', AppendixStaticImage::class);

        yield MenuItem::section('Каталог');
        yield MenuItem::linkToCrud('Оборудование', 'fa fa-broadcast-tower', Equipment::class);
        yield MenuItem::linkToCrud('Операторы', 'fa fa-building', Operator::class);
        yield MenuItem::linkToCrud('Заказчики', 'fa fa-handshake', Customer::class);

        yield MenuItem::subMenu('Сортамент', 'fa fa-ruler-combined')->setSubItems([
            MenuItem::linkToCrud('Уголок равнополочный', 'fa fa-angle-right', GaugeAngleEqual::class),
            MenuItem::linkToCrud('Швеллер',              'fa fa-angle-right', GaugeChannel::class),
            MenuItem::linkToCrud('Двутавр',              'fa fa-angle-right', GaugeIBeam::class),
            MenuItem::linkToCrud('Труба круглая',        'fa fa-circle',      GaugePipeRound::class),
            MenuItem::linkToCrud('Труба квадратная',     'fa fa-square',      GaugePipeSquare::class),
            MenuItem::linkToCrud('Пруток круглый',       'fa fa-minus',       GaugeRoundSolid::class),
        ]);
    }
}
