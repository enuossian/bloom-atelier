<?php

namespace App\Form\Admin;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ServiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    // 'placeholder' => 'Ex: Shopping des essentiels beauté',
                    'class' => 'form-control',
                    'autofocus' => 'autofocus',
                ],
            ])
            ->add('price', MoneyType::class, [
                // la valeur par défaut est float mais Doctrine utilise string pour le type decimal
                'input' => 'string',
                'attr' => [
                    'class' => 'form-control',
                    // 'placeholder' => 'Ex: 150'
                ],
                'currency' => false,
            ])
            ->add('duration', IntegerType::class, [
                'required' => true,
                'attr' => [
                    // 'placeholder' => 'Ex: 180',
                    'class' => 'form-control',
                    'min' => 60,
                    'max' => 300,
                    'step' => 30,
                ],
            ])
            ->add('shortDescription', TextareaType::class, [
                'attr' => [
                    // 'placeholder' => 'Décrivez votre service en quelques mots...',
                    'class' => 'form-control',
                    'rows' => 2,
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    // 'placeholder' => 'Décrivez votre service en détail...',
                    'class' => 'form-control',
                    'rows' => 8,
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                // désactive la validation html
                'required' => false,
                // autoriser la suppression de l'image
                'allow_delete' => true,
                // le label de suppression
                'delete_label' => "Supprimer l'image ?",

                // possibilité de télécharger l'image : false
                'download_label' => false,
                'download_uri' => false,

                // affichage de l'image : false car je gère moi-même pour que ça soit responsive
                'image_uri' => false,

                // définir des pattern à false / associer à asset mapper false
                'imagine_pattern' => false,
                'asset_helper' => false,
                'attr' => [
                    // 'placeholder' => 'Décrivez votre service en détail...',
                    'class' => 'form-control mb-3',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
