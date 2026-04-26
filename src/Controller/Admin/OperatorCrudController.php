<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Operator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OperatorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Operator::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Оператор')
            ->setEntityLabelInPlural('Операторы')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Добавить оператора'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->setLabel('Редактировать'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn(Action $a) => $a->setLabel('Сохранить'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setLabel('Удалить'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Название');
        yield DateTimeField::new('createdAt', 'Создан')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy HH:mm');
    }
}
