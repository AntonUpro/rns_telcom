<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Рабочий email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'name@company.com'
                ]
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('patronymic', TextType::class, [
                'label' => 'Отчество',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Номер телефона',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'phone-input',
                    'type' => 'tel',
                    'placeholder' => '+7 (___) ___-__-__',
                    'maxlength' => '18',
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Пароли не совпадают',
                'first_options' => [
                    'label' => 'Пароль',
                    'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                    'constraints' => [
                        new NotBlank(message: 'Введите пароль'),
                        new Length(min: 6, minMessage: 'Пароль должен быть минимум {{ limit }} символов', max: 4096),
                    ],
                ],
                'second_options' => [
                    'label' => 'Повторите пароль',
                    'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
