<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudController extends AbstractCrudController
{
    private const UPLOAD_DIR = 'var/uploads/signatures';
    private const BASE_PATH  = '/uploads/signatures';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
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
                'Инженер'       => 'ROLE_ENGINEER',
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
        yield TextField::new('lastName', 'Фамилия');
        yield TextField::new('firstName', 'Имя');
        yield TextField::new('patronymic', 'Отчество')->setRequired(false);
        yield ImageField::new('signatureFileName', 'Подпись')
            ->setBasePath(self::BASE_PATH)
            ->setUploadDir(self::UPLOAD_DIR)
            ->setUploadedFileNamePattern('[ulid].[ext]')
            ->setRequired(false);
        yield ChoiceField::new('roles', 'Роли')
            ->setChoices([
                'Администратор' => 'ROLE_ADMIN',
                'Инженер'       => 'ROLE_ENGINEER',
            ])
            ->allowMultipleChoices()
            ->renderExpanded(false);
        yield BooleanField::new('isActive', 'Активен');
//        yield $password;
        yield IntegerField::new('calculations', 'Расчётов')
            ->hideOnForm()
            ->formatValue(fn($v, $entity) => $entity instanceof User ? $entity->getCalculations()->count() : 0);
        yield DateTimeField::new('createdAt', 'Зарегистрирован')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy HH:mm');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setEncodedPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setEncodedPassword($entityInstance);

        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldSignature = $originalData['signatureFileName'] ?? null;

        parent::updateEntity($entityManager, $entityInstance);

        if ($oldSignature !== null && $oldSignature !== $entityInstance->getSignatureFileName()) {
            $path = $this->projectDir . '/' . self::UPLOAD_DIR . '/' . $oldSignature;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function setEncodedPassword(User $user): void
    {
        $plainPassword = $this->getContext()->getRequest()->request->all()['User']['plainPassword']['first'] ?? null;
        if (!empty($plainPassword)) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }
    }
}
