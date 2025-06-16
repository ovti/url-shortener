<?php

/**
 * Url type test.
 */

namespace App\Tests\Type;

use App\Entity\Url;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Form\Type\UrlType;
use App\Service\GuestUserService;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlTypeTest.
 */
class UrlTypeTest extends TypeTestCase
{
    /**
     * Mocks.
     */
    private TagsDataTransformer $tagsDataTransformer;
    private Security $security;
    private GuestUserService $guestUserService;
    private TranslatorInterface $translator;
    private RequestStack $requestStack;
    private Session $session;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        $this->tagsDataTransformer = $this->createMock(TagsDataTransformer::class);
        $this->security = $this->createMock(Security::class);
        $this->guestUserService = $this->createMock(GuestUserService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->session = new Session(new MockArraySessionStorage());
        $this->requestStack = new RequestStack();
        $this->requestStack->push(new Request());
        $this->requestStack->getCurrentRequest()->setSession($this->session);

        parent::setUp();

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->addExtension(new PreloadedExtension([
                new UrlType(
                    $this->tagsDataTransformer,
                    $this->security,
                    $this->guestUserService,
                    $this->translator,
                    $this->requestStack
                ),
            ], []))
            ->getFormFactory();
    }

    /**
     * Test building the form for logged in user.
     */
    public function testBuildFormForLoggedUser(): void
    {
        $this->security->method('getUser')->willReturn($this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')->getMock());

        $form = $this->factory->create(UrlType::class);

        $this->assertFalse($form->has('email'));
        $this->assertTrue($form->has('longUrl'));
        $this->assertTrue($form->has('tags'));

        $this->assertInstanceOf(
            TextType::class,
            $form->get('longUrl')->getConfig()->getType()->getInnerType()
        );

        $this->assertInstanceOf(
            TextType::class,
            $form->get('tags')->getConfig()->getType()->getInnerType()
        );
    }

    /**
     * Test building the form for guest user.
     */
    public function testBuildFormForGuestUser(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $form = $this->factory->create(UrlType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('longUrl'));
        $this->assertTrue($form->has('tags'));

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );
    }

    /**
     * Test form submission for a logged in user.
     */
    public function testSubmitValidDataForLoggedUser(): void
    {
        $this->security->method('getUser')->willReturn($this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')->getMock());
        $this->tagsDataTransformer->method('reverseTransform')->willReturn([]);

        $url = new Url();
        $formData = [
            'longUrl' => 'https://example.com',
            'tags' => 'test, example',
        ];

        $form = $this->factory->create(UrlType::class, $url);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $this->assertEquals('https://example.com', $url->getLongUrl());
    }

    /**
     * Test form submission for a guest user.
     */
    public function testSubmitValidDataForGuestUser(): void
    {
        $this->security->method('getUser')->willReturn(null);
        $this->tagsDataTransformer->method('reverseTransform')->willReturn([]);
        $this->guestUserService->method('countEmailsUsedInLast24Hours')->willReturn(0);

        $url = new Url();
        $formData = [
            'email' => 'guest@example.com',
            'longUrl' => 'https://example.com',
            'tags' => 'test, example',
        ];

        $form = $this->factory->create(UrlType::class, $url);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $this->assertEquals('https://example.com', $url->getLongUrl());
        $this->assertEquals('guest@example.com', $this->session->get('email'));
    }

    /**
     * Test form submission with invalid URL.
     */
    public function testSubmitInvalidUrl(): void
    {
        $this->security->method('getUser')->willReturn($this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')->getMock());

        $url = new Url();
        $formData = [
            'longUrl' => 'not-a-valid-url',
            'tags' => 'test',
        ];

        $form = $this->factory->create(UrlType::class, $url);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
    }

    /**
     * Test form submission for a guest with too many requests.
     */
    public function testSubmitGuestUserExceedsLimit(): void
    {
        $this->security->method('getUser')->willReturn(null);
        $this->guestUserService->method('countEmailsUsedInLast24Hours')->willReturn(10);
        $this->translator->method('trans')->willReturn('You have exceeded the limit of URLs for this email in the last 24 hours.');

        $url = new Url();
        $formData = [
            'email' => 'frequent@example.com',
            'longUrl' => 'https://example.com',
            'tags' => '',
        ];

        $form = $this->factory->create(UrlType::class, $url);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());
    }

    /**
     * Test the block prefix.
     */
    public function testGetBlockPrefix(): void
    {
        $type = new UrlType(
            $this->tagsDataTransformer,
            $this->security,
            $this->guestUserService,
            $this->translator,
            $this->requestStack
        );

        $this->assertEquals('Url', $type->getBlockPrefix());
    }

    /**
     * Get form extensions.
     *
     * @return array Form extensions
     */
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
            new PreloadedExtension([
                new UrlType(
                    $this->tagsDataTransformer,
                    $this->security,
                    $this->guestUserService,
                    $this->translator,
                    $this->requestStack
                ),
            ], []),
        ];
    }
}
