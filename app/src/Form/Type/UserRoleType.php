<?php
/*
 * User role type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserRoleType.
 */
class UserRoleType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<mixed>         $options The options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //update user role
        $builder->add(
            'roles',
            ChoiceType::class,
            [
                'label' => 'label_role',
                'required' => true,
                'choices' => [
                    'label_user' => 'ROLE_USER',
                    'label_admin' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
            ]

        );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver<mixed> $resolver The resolver for the options
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}