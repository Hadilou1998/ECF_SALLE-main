<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la salle'
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacité'
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
                'label' => 'Critères ergonomiques'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
