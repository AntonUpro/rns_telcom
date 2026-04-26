<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugeChannel;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GaugeChannelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugeChannel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Швеллер')
            ->setEntityLabelInPlural('Швеллер')
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
        yield NumberField::new('height', 'h, мм (высота)')->setNumDecimals(2);
        yield NumberField::new('flangeWidth', 'b, мм (полка)')->setNumDecimals(2);
        yield NumberField::new('webThickness', 'd, мм (стенка)')->setNumDecimals(2);
        yield NumberField::new('flangeThickness', 't, мм (полка)')->setNumDecimals(2);
        yield NumberField::new('area', 'A, см²')->setNumDecimals(3);
        yield NumberField::new('massPerMeter', 'Масса, кг/м')->setNumDecimals(3)->hideOnIndex();
        yield NumberField::new('centroidDistanceZ', 'z₀, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaX', 'Ix, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaY', 'Iy, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaX', 'ix, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaY', 'iy, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceX', 'Wx, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceY', 'Wy, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceYNear', 'Wy1, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('staticMomentX', 'Sx, см³')->setNumDecimals(2)->hideOnIndex();
    }
}
