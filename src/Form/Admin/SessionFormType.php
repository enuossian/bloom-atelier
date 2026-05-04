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
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class SessionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'placeholder' => '--Selectionnez un service--',
                'disabled' => $options['edit_mode'],
                'attr' => [
                    'class' => 'form-control',
                    'autofocus' => 'autofocus',
                ],
            ])
            ->add('startTime', DateTimeType::class, [
                'widget' => 'single_text',
                'disabled' => $options['edit_mode'],
                'empty_data' => null,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('endTime', DateTimeType::class, [
                'widget' => 'single_text',
                'disabled' => $options['edit_mode'],
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
                    'min' => $options['current_participants'],
                ],
                'constraints' => [
                    new GreaterThanOrEqual(
                        value: $options['current_participants'],
                        message: 'Vous ne pouvez pas descendre en dessous du nombre actuel de participants.',
                    ),
                ],
            ])
            ->add('notes', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            // mode édition à false par defaut car création, on le passe à true dans le controller pour la modification
            'edit_mode' => false,
            // nombre de participants actuels, on initialise à 0 par défaut
            'current_participants' => 0,
        ]);
    }
}
