<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugeChannel;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class GaugeChannelCrudController extends AbstractGaugeCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugeChannel::class;
    }

    protected function profileTypeCode(): GaugeProfileTypeEnum
    {
        return GaugeProfileTypeEnum::CHANNEL;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Швеллер')
            ->setEntityLabelInPlural('Швеллер');
    }

    public function configureFields(string $pageName): iterable
    {
        yield from $this->profileFields();

        yield NumberField::new('height', 'h, мм (высота)')->setNumDecimals(2);
        yield NumberField::new('flangeWidth', 'b, мм (полка)')->setNumDecimals(2);
        yield NumberField::new('webThickness', 'd, мм (стенка)')->setNumDecimals(2);
        yield NumberField::new('flangeThickness', 't, мм (полка)')->setNumDecimals(2);
        yield NumberField::new('innerFilletRadius', 'R, мм (закругление)')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('edgeFilletRadius', 'r, мм (кромка полки)')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('area', 'A, см²')->setNumDecimals(3);
        yield NumberField::new('massPerMeter', 'Масса, кг/м')->setNumDecimals(3)->hideOnIndex();
        yield NumberField::new('centroidDistanceZ', 'z₀, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaX', 'Ix, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaY', 'Iy, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaX', 'ix, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaY', 'iy, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceX', 'Wx, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceY', 'Wy, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceYNear', 'W\'y, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('staticMomentX', 'Sx, см³')->setNumDecimals(2)->hideOnIndex();
    }
}
