<?php

/**
 * Registration type test.
 */

namespace App\Tests\Type;

use App\Form\Type\RegistrationType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

/**
 * Class RegistrationTypeTest.
 */
class RegistrationTypeTest extends TypeTestCase
{
    /**
     * Form factory.
     */
    private FormFactoryInterface $formFactory;

    /**
     * Set up test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtensions([new ValidatorExtension($validator)])
            ->getFormFactory();
    }

    /**
     * Test building the form.
     */
    public function testBuildFormHasExpectedFieldsAndTypes(): void
    {
        $form = $this->formFactory->create(RegistrationType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));

        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );

        $passwordConfig = $form->get('password')->getConfig();
        $this->assertInstanceOf(
            RepeatedType::class,
            $passwordConfig->getType()->getInnerType()
        );
        $this->assertSame(
            PasswordType::class,
            $passwordConfig->getOptions()['type']
        );
    }

    /**
     * Test the form is configured correctly.
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'user@example.com',
            'password' => [
                'first' => 'secret123',
                'second' => 'secret123',
            ],
        ];

        $form = $this->formFactory->create(RegistrationType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();
        $this->assertSame('user@example.com', $data['email']);
        $this->assertSame('secret123', $data['password']);
    }

    /**
     * Test submitting invalid data.
     */
    public function testSubmitInvalidData(): void
    {
        $form = $this->formFactory->create(RegistrationType::class);
        $form->submit(['email' => '', 'password' => ['first' => '', 'second' => '']]);
        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, $form->get('email')->getErrors(true)->count());
        $this->assertGreaterThan(0, $form->get('password')->getErrors(true)->count());

        $form = $this->formFactory->create(RegistrationType::class);
        $form->submit(['email' => 'ab', 'password' => ['first' => '123', 'second' => '123']]);
        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, $form->get('email')->getErrors(true)->count());
        $this->assertGreaterThan(0, $form->get('password')->getErrors(true)->count());
    }

    /**
     * Test the block prefix.
     */
    public function testGetBlockPrefix(): void
    {
        $type = new RegistrationType();
        $this->assertSame('user', $type->getBlockPrefix());
    }
}
