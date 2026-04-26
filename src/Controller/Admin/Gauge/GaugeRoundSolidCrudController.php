<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugeRoundSolid;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GaugeRoundSolidCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugeRoundSolid::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пруток круглый')
            ->setEntityLabelInPlural('Пруток круглый')
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
        yield NumberField::new('diameter', 'd, мм')->setNumDecimals(2);
        yield NumberField::new('area', 'A, см²')->setNumDecimals(3);
        yield NumberField::new('massPerMeter', 'Масса, кг/м')->setNumDecimals(3)->hideOnIndex();
        yield NumberField::new('momentInertia', 'I, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertia', 'i, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistance', 'W, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('polarMomentInertia', 'Ip, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('polarMomentResistance', 'Wp, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('plasticMomentResistance', 'Wpl, см³')->setNumDecimals(2)->hideOnIndex();
    }
}
