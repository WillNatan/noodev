<?php

namespace App\Form;

use App\Entity\Articles;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArticlesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body', TextareaType::class, ['attr'=>[]])
            ->add('thumbnail_img', FileType::class,
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
            ->add('title', TextType::class, ['attr'=>['class'=>'form-control']])
            ->add('description', TextType::class, ['attr'=>['class'=>'form-control']])
            ->add('Category', EntityType::class, ['class'=>Category::class, 'placeholder'=>'Choisissez une catégorie','choice_label'=>'name', 'attr'=>['class'=>'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
        ]);
    }
}
