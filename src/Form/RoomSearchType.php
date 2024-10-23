<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class RoomSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Nom de la salle'
            ])
            ->add('capacity', IntegerType::class, [
                'required' => false,
                'label' => 'Capacité minimale',
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('equipment', ChoiceType::class, [
                'choices' => [
                    'Projecteur' => 'projector',
                    'Tableau blanc' => 'whiteboard',
                    'Ordinateur' => 'computer',
                    'Système de visioconférence' => 'video_conference',
                    'Écran tactile' => 'touch_screen'
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Équipements'
            ])
            ->add('ergonomics', ChoiceType::class, [
                'choices' => [
                    'Luminosité naturelle' => 'natural_light',
                    'Accessibilité PMR' => 'wheelchair_accessible',
                    'Climatisation' => 'air_conditioning',
                    'Insonorisation' => 'soundproof',
                    'Mobilier ergonomique' => 'ergonomic_furniture'
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Critères ergonomiques'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
            'method' => 'POST',
        ]);
    }
}