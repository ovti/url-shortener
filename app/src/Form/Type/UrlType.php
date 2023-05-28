<?php

namespace App\Form\Type;

use App\Entity\Url;
use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class UrlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'long_url',
            TextType::class,
            [
                'label' => 'label.long_url',
                'attr' => ['placeholder' => 'label.long_url'],
            ]
        );
        $builder->add(
            'tags',
            EntityType::class,
            [
                'class' => Tag::class,
                'choice_label' => function ($tag): string {
                    return $tag->getName();
                },
                'label' => 'label.tags',
                'placeholder' => 'label.none',
                'required' => true,
                'expanded' => true,
                'multiple' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Url::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'url';
    }
}
