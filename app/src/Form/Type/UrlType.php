<?php

/**
 * Url type.
 */

namespace App\Form\Type;

use App\Entity\Url;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Service\GuestUserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlType.
 */
class UrlType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param TagsDataTransformer $tagsDataTransformer Tags data transformer
     * @param Security            $security            Security
     * @param GuestUserService    $guestUserService    Guest user service
     * @param TranslatorInterface $translator          Translator
     * @param RequestStack        $requestStack        Request stack
     */
    public function __construct(private readonly TagsDataTransformer $tagsDataTransformer, private readonly Security $security, private readonly GuestUserService $guestUserService, private readonly TranslatorInterface $translator, private readonly RequestStack $requestStack)
    {
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$this->security->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            $builder->add(
                'email',
                EmailType::class,
                [
                    'label' => 'label.email',
                    'required' => true,
                    'mapped' => false,
                    'attr' => ['max_length' => 191],
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 3, 'max' => 191]),
                        new Email(),
                    ],
                ]
            );
            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $email = $event->getForm()->get('email')->getData();
                $request = $this->requestStack->getSession();
                $request->set('email', $email);

                $count = $this->guestUserService->countEmailsUsedInLast24Hours($email);
                if ($count >= 10) {
                    $event->getForm()->addError(new FormError($this->translator->trans('message.email_limit_exceeded')));
                }
            });
        }

        $builder->add(
            'longUrl',
            TextType::class,
            [
                'label' => 'label.long_url',
                'required' => true,
                'attr' => ['max_length' => 255],
            ]
        );
        $builder->add(
            'tags',
            TextType::class,
            [
                'label' => 'label.tags',
                'required' => false,
                'attr' => ['max_length' => 64],
            ]
        );

        $builder->get('tags')->addModelTransformer(
            $this->tagsDataTransformer
        );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Url::class,
        ]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'Url';
    }
}
