<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugeAngleEqual;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class GaugeAngleEqualCrudController extends AbstractGaugeCrudController
{
    public static function getEntityFqcn(): string
    {
        return GaugeAngleEqual::class;
    }

    protected function profileTypeCode(): GaugeProfileTypeEnum
    {
        return GaugeProfileTypeEnum::ANGLE_EQUAL;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Уголок равнополочный')
            ->setEntityLabelInPlural('Уголок равнополочный');
    }

    public function configureFields(string $pageName): iterable
    {
        yield from $this->profileFields();

        yield NumberField::new('flangeWidth', 'b, мм (полка)')->setNumDecimals(2);
        yield NumberField::new('flangeThickness', 't, мм (толщина полки)')->setNumDecimals(2);
        yield NumberField::new('innerFilletRadius', 'R, мм (закругление у корня)')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('edgeFilletRadius', 'r, мм (кромка полки)')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('area', 'A, см²')->setNumDecimals(3);
        yield NumberField::new('massPerMeter', 'Масса, кг/м')->setNumDecimals(3)->hideOnIndex();
        yield NumberField::new('centroidDistanceX', 'z₀x, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('centroidDistanceY', 'z₀y, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaX', 'Ix, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaY', 'Iy, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaMax', 'Imax (J₁), см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentInertiaMin', 'Imin (J₂), см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('centrifugalMomentInertia', 'Jxy, см⁴')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaX', 'ix, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaY', 'iy, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaMin', 'imin, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('radiusInertiaMax', 'imax, см')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceX', 'Wx, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceY', 'Wy, см³')->setNumDecimals(2)->hideOnIndex();
        yield NumberField::new('momentResistanceMin', 'Wmin, см³')->setNumDecimals(2)->hideOnIndex();
    }
}
