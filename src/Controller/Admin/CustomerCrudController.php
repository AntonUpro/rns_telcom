<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Customer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Заказчик')
            ->setEntityLabelInPlural('Заказчики')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'code']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Добавить заказчика'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Название');
        yield TextField::new('code', 'Код')->setRequired(false);
        yield BooleanField::new('isActive', 'Активен');
        yield DateTimeField::new('createdAt', 'Создано')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy HH:mm');
        yield DateTimeField::new('updatedAt', 'Обновлено')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy HH:mm');
    }
}
