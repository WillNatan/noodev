<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('avatar', FileType::class,
                [
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png'
                            ],
                            'mimeTypesMessage' => 'Les images sont uniquement téléchargeables',
                        ])
                    ],
                    'required' => false,

                    'data_class'=>null,
                    'label'=>'Choisir une image','label_attr'=>['class'=>'text-light mb-0']
                ])
            ->add('email', EmailType::class, ['attr'=>['class'=>'form-control']])
            ->add('username', TextType::class, ['attr'=>['class'=>'form-control']])
            ->add('description', TextType::class, ['attr'=>['class'=>'form-control']])
            ->add('roles', ChoiceType::class, ['choices'=>
                [
                    'Administrateur'=>'ROLE_ADMIN',
                    'Utilisateur'=>'ROLE_USER'
                ],
                    'multiple'=>true,
                    'attr'=>[
                        'class'=>'form-control',
                        'data-style'=>'select-with-transition',
                        'title'=>'Choisissez un rôle',
                        'size'=>'8'
                    ]

            ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
