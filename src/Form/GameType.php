<?php

namespace App\Form;

use App\Entity\Game;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description',CKEditorType::class, [
                'config_name' => 'game',
            ])
            ->add('dateStart', DateType::class, [
                'widget'=>'single_text',
                'required'=>false,

            ])
            ->add('dateEnd',DateType::class, [
                'widget'=>'single_text',
                'required'=>false,

            ])
            ->add('adress')
            ->add('disclaimer',TextareaType::class,[
                'required' => false
            ])
            ->add('postcode')
            ->add('city')
            ->add('banner', FileType::class, [
                'required' =>false,
                'mapped' => false,
                'constraints' => [

                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Fichier accépté : "jpeg, jpg, png, gif, webp"',
                        'maxSizeMessage' => 'Merci de ne pas dépasser {{ limit }} {{ suffixe }}',

                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}


