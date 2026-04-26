<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugePipeSquare;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GaugePipeSquareCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugePipeSquare::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Труба квадратная')
            ->setEntityLabelInPlural('Труба квадратная')
            ->setDefaultSort(['profile' => 'ASC'])
            ->setPaginatorUseOutputWalkers(true)
            ->setSearchFields(['profile.name', 'profile.designation']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('profile', 'Профиль')
            ->setFormTypeOptions(['required' => true]);
        yield TextField::new('profile.name', 'Наименование')->hideOnForm();
        yield TextField::new('profile.designation', 'Обозначение')->hideOnForm();
        yield NumberField::new('outerSide', 'a, мм (сторона)')->setNumDecimals(2);
        yield NumberField::new('wallThickness', 't, мм (стенка)')->setNumDecimals(2);
        yield NumberField::new('area', 'A, см²')->setNumDecimals(3);
        yield NumberField::new('massPerMeter', 'Масса, кг/м')->setNumDecimals(3)->hideOnIndex();
        yield NumberField::new('momentInertia', 'I, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertia', 'i, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistance', 'W, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('plasticMomentResistance', 'Wpl, см³')->setNumDecimals(2)->hideOnIndex();
    }
}
