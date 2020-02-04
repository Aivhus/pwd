<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Category;
use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;



class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('ingredient')
            ->add('step')
            ->add('image', FileType::class, [
                'label' => 'upload une image',
                'data_class' => null,
            ])
            ->add('category', EntityType::class , ['class' => Category::class, 'choice_label' => 'title', 'multiple' => false])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
