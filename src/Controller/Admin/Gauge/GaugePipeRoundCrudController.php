<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugePipeRound;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class GaugePipeRoundCrudController extends AbstractGaugeCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugePipeRound::class;
    }

    protected function profileTypeCode(): GaugeProfileTypeEnum
    {
        return GaugeProfileTypeEnum::PIPE_ROUND;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Труба круглая')
            ->setEntityLabelInPlural('Труба круглая');
    }

    public function configureFields(string $pageName): iterable
    {
        yield from $this->profileFields();

        yield NumberField::new('outerDiameter', 'D, мм (нар.)')->setNumDecimals(2);
        yield NumberField::new('wallThickness', 't, мм (стенка)')->setNumDecimals(2);
        yield NumberField::new('area', 'A, см²')->setNumDecimals(3);
        yield NumberField::new('massPerMeter', 'Масса, кг/м')->setNumDecimals(3)->hideOnIndex();
        yield NumberField::new('momentInertia', 'I, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertia', 'i, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistance', 'W, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('plasticMomentResistance', 'Wpl, см³')->setNumDecimals(2)->hideOnIndex();
    }
}
