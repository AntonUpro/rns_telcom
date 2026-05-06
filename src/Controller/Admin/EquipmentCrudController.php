<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Equipment;
use App\Enum\Equipment\EquipmentTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class EquipmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Equipment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Оборудование')
            ->setEntityLabelInPlural('Оборудование')
            ->setDefaultSort(['brand' => 'ASC', 'model' => 'ASC'])
            ->setSearchFields(['brand', 'model', 'fullName']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Добавить оборудование'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        $typeChoices = [];
        foreach (EquipmentTypeEnum::cases() as $case) {
            $typeChoices[$case->label()] = $case->value;
        }

        return $filters
            ->add(ChoiceFilter::new('type')->setChoices($typeChoices)->setLabel('Тип'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('brand', 'Бренд');
        yield TextField::new('model', 'Модель');
        yield ChoiceField::new('type', 'Тип')
            ->setChoices(array_combine(
                array_map(fn($c) => $c->label(), EquipmentTypeEnum::cases()),
                EquipmentTypeEnum::cases()
            ));
        yield BooleanField::new('hasDiameter', 'Есть диаметр');
        yield NumberField::new('diameter', 'Диаметр, м')
            ->setNumDecimals(1);
        yield NumberField::new('height', 'Высота, м')
            ->setNumDecimals(1);
        yield NumberField::new('width', 'Ширина, м')
            ->setNumDecimals(1);
        yield NumberField::new('depth', 'Глубина, м')
            ->setNumDecimals(1);
        yield NumberField::new('weight', 'Масса, кг')
            ->setNumDecimals(1);
        yield DateTimeField::new('createdAt', 'Создано')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy');
    }
}
