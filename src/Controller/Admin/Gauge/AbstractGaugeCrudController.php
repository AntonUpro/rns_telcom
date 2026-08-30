<?php

declare(strict_types=1);

namespace App\Controller\Admin\Gauge;

use App\Entity\Gauge\GaugeProfile;
use App\Enum\Gauge\GaugeProfileTypeEnum;
use App\Repository\Gauge\GaugeProfileTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Общая логика CRUD-контроллеров сортамента.
 *
 * У всех сущностей сортамента profile_id — одновременно PRIMARY KEY и FOREIGN KEY
 * на gauge_profile. Поэтому:
 *   - при создании новой записи объект GaugeProfile должен существовать до
 *     привязки полей формы (profile.name, profile.type…) — см. createEntity();
 *   - идентификатором записи в URL EasyAdmin выступает числовой gauge_profile.id
 *     (GaugeProfile::__toString() возвращает id), иначе find() получает строку
 *     вида «Уголок 75×6» и падает на приведении к bigint.
 */
abstract class AbstractGaugeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly GaugeProfileTypeRepository $profileTypeRepository,
    ) {
    }

    /** Тип профиля, к которому относится этот сортамент. */
    abstract protected function profileTypeCode(): GaugeProfileTypeEnum;

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['profile' => 'ASC'])
            ->setPaginatorUseOutputWalkers(true)
            ->setSearchFields(['profile.name', 'profile.designation']);
    }

    public function createEntity(string $entityFqcn)
    {
        $profile = new GaugeProfile();
        $profile->setType($this->profileTypeRepository->findByCode($this->profileTypeCode()));
        $profile->setName('');
        $profile->setDesignation('');

        $entity = new $entityFqcn();
        $entity->setProfile($profile);

        return $entity;
    }

    /** Редактируемые поля связанного профиля (таблица gauge_profile). */
    protected function profileFields(): iterable
    {
        yield FormField::addFieldset('Профиль');
        yield TextField::new('profile.designation', 'Обозначение');
        yield TextField::new('profile.name', 'Наименование');
        yield TextField::new('profile.standard', 'Нормативный документ')
            ->setRequired(false)
            ->onlyOnForms();
        // Тип профиля жёстко задан видом сортамента (см. createEntity()) —
        // показываем только для справки. AssociationField не поддерживает
        // вложенный путь profile.type, поэтому обычное поле, отключённое к вводу.
        yield TextField::new('profile.type.name', 'Тип профиля')
            ->setFormTypeOption('disabled', true)
            ->onlyOnForms();
        yield BooleanField::new('profile.isCustom', 'Пользовательский профиль')->onlyOnForms();
        yield FormField::addFieldset('Характеристики сечения');
    }
}
