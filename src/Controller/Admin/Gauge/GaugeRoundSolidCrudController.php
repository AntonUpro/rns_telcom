<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugeRoundSolid;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class GaugeRoundSolidCrudController extends AbstractGaugeCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugeRoundSolid::class;
    }

    protected function profileTypeCode(): GaugeProfileTypeEnum
    {
        return GaugeProfileTypeEnum::CIRCLE;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Пруток круглый')
            ->setEntityLabelInPlural('Пруток круглый');
    }

    public function configureFields(string $pageName): iterable
    {
        yield from $this->profileFields();

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
