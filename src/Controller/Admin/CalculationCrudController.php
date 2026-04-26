<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Calculation;
use App\Enum\CalculationStatusEnum;
use App\Enum\CalculationTypeEnum;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class CalculationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Calculation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Расчёт')
            ->setEntityLabelInPlural('Расчёты')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['name'])
            ->setPaginatorUseOutputWalkers(true)
            ->setPaginatorFetchJoinCollection(true)
            ->showEntityActionsInlined();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // JOIN inverse-side OneToOne associations so that ObjectHydrator marks them
        // as "fetched" and UnitOfWork skips loadOneToOneEntity (which fails for inverse sides).
        $qb->leftJoin('entity.calculationData', 'calculationData')
            ->addSelect('calculationData')
            ->leftJoin('entity.pillarPlatform', 'pillarPlatform')
            ->addSelect('pillarPlatform');

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('user')->setLabel('Пользователь'))
            ->add(ChoiceFilter::new('status')->setChoices(CalculationStatusEnum::choices())->setLabel('Статус'))
            ->add(ChoiceFilter::new('type')->setChoices(CalculationTypeEnum::choices())->setLabel('Тип'))
            ->add(DateTimeFilter::new('createdAt')->setLabel('Дата создания'))
            ->add(DateTimeFilter::new('deletedAt')->setLabel('Удалён'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Название');
        yield AssociationField::new('user', 'Пользователь');
        yield ChoiceField::new('type', 'Тип')
            ->setChoices(array_combine(
                array_map(fn($c) => $c->label(), CalculationTypeEnum::cases()),
                CalculationTypeEnum::cases()
            ));
        yield ChoiceField::new('status', 'Статус')
            ->setChoices(array_combine(
                array_map(fn($c) => $c->label(), CalculationStatusEnum::cases()),
                CalculationStatusEnum::cases()
            ))
            ->renderAsBadges([
                CalculationStatusEnum::DRAFT->value => 'secondary',
                CalculationStatusEnum::CALCULATED->value => 'info',
                CalculationStatusEnum::APPROVED->value   => 'success',
                CalculationStatusEnum::ARCHIVED->value   => 'warning',
            ]);
        yield DateTimeField::new('createdAt', 'Создан')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy HH:mm');
        yield DateTimeField::new('deletedAt', 'Удалён')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('dd.MM.yyyy HH:mm');
    }
}
