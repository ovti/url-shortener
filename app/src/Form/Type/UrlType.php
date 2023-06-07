<?php
/**
 * Url type.
 */

namespace App\Form\Type;

use App\Entity\Url;
use App\Entity\Tag;
use App\Form\DataTransformer\TagsDataTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use App\Entity\GuestUser;
use App\Service\GuestUserService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\GuestUserRepository;


/**
 * Class TagType.
 */
class UrlType extends AbstractType
{

    /**
     * Tags data transformer.
     *
     * @var TagsDataTransformer
     */
    private TagsDataTransformer $tagsDataTransformer;

    /**
     * Security.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Guest user service.
     *
     * @var GuestUserService
     */
    private GuestUserService $guestUserService;

    /**
     * Session.
     *
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * Guest user repository.
     *
     * @var GuestUserRepository
     */
    private GuestUserRepository $guestUserRepository;


    /**
     * Constructor.
     *
     * @param TagsDataTransformer $tagsDataTransformer Tags data transformer
     * @param Security $security Security
     * @param GuestUserService $guestUserService Guest user service
     * @param SessionInterface $session Session
     * @param GuestUserRepository $guestUserRepository Guest user repository
     */
    public function __construct(TagsDataTransformer $tagsDataTransformer, Security $security, GuestUserService $guestUserService, SessionInterface $session, GuestUserRepository $guestUserRepository)
    {
        $this->tagsDataTransformer = $tagsDataTransformer;
        $this->security = $security;
        $this->guestUserService = $guestUserService;
        $this->session = $session;
        $this->guestUserRepository = $guestUserRepository;
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
        if (!$this->security->getUser()) {
            $builder->add(
                'email',
                TextType::class,
                [
                    'label' => 'label.email',
                    'required' => true,
                    'mapped' => false,
                    'attr' => ['max_length' => 64],
                ]
            );
            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $email = $event->getForm()->get('email')->getData();
                $this->session->set('email', $email);
                $guestUser = new GuestUser();
                $guestUser->setEmail($email);

                $count = $this->guestUserService->countEmailsUsedInLast24Hours($email);
                if ($count >= 2) {
                    $event->getForm()->get('email')->addError(new FormError('error.too_many_emails' . $count . $email));
                }

                $this->guestUserService->save($guestUser);


            });
        }


        $builder->add(
            'longUrl',
            TextType::class,
            [
                'label' => 'label.long_url',
                'required' => true,
                'attr' => ['max_length' => 64],
            ]);
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
