<?php

namespace App\Tests\Type;

use App\Form\Type\RegistrationType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testBuildFormHasExpectedFieldsAndTypes(): void
    {
        $form = $this->factory->create(RegistrationType::class);

        // pola
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('password'));

        // typ pola email
        $this->assertInstanceOf(
            EmailType::class,
            $form->get('email')->getConfig()->getType()->getInnerType()
        );

        // typ pola password to RepeatedType z typem PasswordType
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

    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'user@example.com',
            'password' => [
                'first' => 'secret123',
                'second' => 'secret123',
            ],
        ];

        $form = $this->factory->create(RegistrationType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();
        $this->assertSame('user@example.com', $data['email']);
        $this->assertSame('secret123', $data['password']);
    }

    public function testSubmitInvalidData(): void
    {
        // puste wartości
        $form = $this->factory->create(RegistrationType::class);
        $form->submit(['email' => '', 'password' => ['first' => '', 'second' => '']]);
        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, $form->get('email')->getErrors(true)->count());
        $this->assertGreaterThan(0, $form->get('password')->getErrors(true)->count());

        // za krótkie wartości
        $form = $this->factory->create(RegistrationType::class);
        $form->submit(['email' => 'ab', 'password' => ['first' => '123', 'second' => '123']]);
        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, $form->get('email')->getErrors(true)->count());
        $this->assertGreaterThan(0, $form->get('password')->getErrors(true)->count());
    }

    public function testGetBlockPrefix(): void
    {
        $type = new RegistrationType();
        $this->assertSame('user', $type->getBlockPrefix());
    }
}
