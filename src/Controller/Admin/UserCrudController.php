<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пользователь')
            ->setEntityLabelInPlural('Пользователи')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorUseOutputWalkers(true)
            ->setSearchFields(['email', 'firstName', 'lastName']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Добавить пользователя'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setLabel('Удалить'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isActive')->setLabel('Активен'))
            ->add(ChoiceFilter::new('roles')->setChoices([
                'Администратор' => 'ROLE_ADMIN',
                'Пользователь'  => 'ROLE_USER',
            ])->setLabel('Роль'));
    }

    public function configureFields(string $pageName): iterable
    {
        $password = TextField::new('plainPassword', 'Пароль')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'Новый пароль'],
                'second_options'  => ['label' => 'Повторите пароль'],
                'mapped'          => false,
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms();

        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email', 'Email');
        yield TextField::new('firstName', 'Имя');
        yield TextField::new('lastName', 'Фамилия');
        yield ChoiceField::new('roles', 'Роли')
            ->setChoices([
                'Администратор' => 'ROLE_ADMIN',
                'Пользователь'  => 'ROLE_USER',
            ])
            ->allowMultipleChoices()
            ->renderExpanded(false);
        yield BooleanField::new('isActive', 'Активен');
        yield $password;
        yield IntegerField::new('calculations', 'Расчётов')
            ->hideOnForm()
            ->formatValue(fn($v, $entity) => $entity instanceof User ? $entity->getCalculations()->count() : 0);
        yield DateTimeField::new('createdAt', 'Зарегистрирован')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy HH:mm');
    }

    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setEncodedPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setEncodedPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function setEncodedPassword(User $user): void
    {
        $plainPassword = $this->getContext()->getRequest()->request->all()['User']['plainPassword']['first'] ?? null;
        if (!empty($plainPassword)) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }
    }
}
