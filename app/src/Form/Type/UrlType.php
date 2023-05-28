<?php

namespace App\Form\Type;

use App\Entity\Url;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UrlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'longUrl',
            TextType::class,
            [
                'label' => 'label.long_url',
                'required' => true,
                'attr' => ['max_length' => 255],
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
