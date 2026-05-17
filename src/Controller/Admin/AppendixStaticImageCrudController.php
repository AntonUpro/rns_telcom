<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AppendixStaticImage;
use App\Enum\AppendixTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppendixStaticImageCrudController extends AbstractCrudController
{
    private const UPLOAD_DIR = 'var/uploads/appendix_images';
    private const BASE_PATH  = '/uploads/appendix_images';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return AppendixStaticImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Изображение приложения')
            ->setEntityLabelInPlural('Изображения приложений (Сертификаты, СРО, НОПРИЗ)')
            ->setDefaultSort(['appendixType' => 'ASC', 'position' => 'ASC'])
            ->setSearchFields([]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Добавить изображение'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield ChoiceField::new('appendixType', 'Приложение')
            ->setChoices(array_combine(
                array_map(fn(AppendixTypeEnum $c) => $c->label(), AppendixTypeEnum::cases()),
                AppendixTypeEnum::cases()
            ));

        yield NumberField::new('position', 'Порядок')
            ->setHelp('Меньшее число — выше в документе');

        yield ImageField::new('fileName', 'Изображение')
            ->setBasePath(self::BASE_PATH)
            ->setUploadDir(self::UPLOAD_DIR)
            ->setUploadedFileNamePattern('[ulid].[ext]')
            ->setRequired(Crud::PAGE_NEW === $pageName);

        yield DateTimeField::new('createdAt', 'Добавлено')
            ->hideOnForm()
            ->setFormat('dd.MM.yyyy HH:mm');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->deleteFileFromDisk($entityInstance->getFileName());
        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $oldFileName  = $originalData['fileName'] ?? null;

        parent::updateEntity($entityManager, $entityInstance);

        if ($oldFileName !== null && $oldFileName !== $entityInstance->getFileName()) {
            $this->deleteFileFromDisk($oldFileName);
        }
    }

    private function deleteFileFromDisk(string $fileName): void
    {
        $path = $this->projectDir . '/' . self::UPLOAD_DIR . '/' . $fileName;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
