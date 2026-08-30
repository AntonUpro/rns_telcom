<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugeAngleEqual;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GaugeAngleEqualCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugeAngleEqual::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Уголок равнополочный')
            ->setEntityLabelInPlural('Уголок равнополочный')
            ->setDefaultSort(['profile' => 'ASC'])
            ->setPaginatorUseOutputWalkers(true)
            ->setSearchFields(['profile.name', 'profile.designation']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('profile', 'Профиль')
            ->setFormTypeOptions(['required' => true]);
//        yield TextField::new('profile.name', 'Наименование')->hideOnForm();
//        yield TextField::new('profile.designation', 'Обозначение')->hideOnForm();
//        yield NumberField::new('flangeWidth', 'b, мм (полка)')->setNumDecimals(2);
//        yield NumberField::new('flangeThickness', 't, мм (толщина)')->setNumDecimals(2);
        yield NumberField::new('area', 'A, см²')->setNumDecimals(3);
        yield NumberField::new('massPerMeter', 'Масса, кг/м')->setNumDecimals(3)->hideOnForm();
        yield NumberField::new('centroidDistanceX', 'z₀x, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('centroidDistanceY', 'z₀y, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaX', 'Ix, см⁴')->setNumDecimals(2)->hideOnForm();
        yield NumberField::new('momentInertiaY', 'Iy, см⁴')->setNumDecimals(2)->hideOnForm();
        yield NumberField::new('momentInertiaMin', 'Imin, см⁴')->setNumDecimals(2)->hideOnForm();
        yield NumberField::new('radiusInertiaX', 'ix, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaY', 'iy, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaMin', 'imin, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceX', 'Wx, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceY', 'Wy, см³')->setNumDecimals(2)->hideOnIndex();
    }
}
