<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateTimeType::class, [
                'required' => false,
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'required' => false,
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'pending',
                    'Confirmée' => 'confirmed',
                    'Annulée' => 'cancelled',
                ],
                'required' => false,
                'label' => 'Statut',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
        ]);
    }
}