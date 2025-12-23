<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('owner', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
