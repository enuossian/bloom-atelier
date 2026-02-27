<?php

namespace App\Form\Admin;

use App\Entity\Service;
use App\Entity\Session;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('reference')
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'placeholder' => '--Selectionnez un service--',
                'attr' => [
                    'class' => 'form-control',
                    'autofocus' => 'autofocus',
                ],
            ])
            ->add('startTime', DateTimeType::class, [
                'widget' => 'single_text',
                'empty_data' => null,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('endTime', DateTimeType::class, [
                'widget' => 'single_text',
                'empty_data' => null,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('location', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('maxParticipants', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            // ->add('status')
            ->add('notes', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            // ->add('createdAt', null, [
            //    'widget' => 'single_text',
            // ])
            // ->add('updatedAt', null, [
            //    'widget' => 'single_text',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
