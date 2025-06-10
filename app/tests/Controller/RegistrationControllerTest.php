<?php

namespace App\Tests\Controller;

use App\Controller\RegistrationController;
use App\Entity\User;
use App\Form\Type\RegistrationType;
use App\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationControllerTest extends TestCase
{
    public function testRegisterWithValidForm(): void
    {
        $request = new Request([], [
            'registration_form' => [ // Assuming your form's root name is 'registration_form'
                'email' => 'test@example.com',
                'password' => [
                    'first' => 'password123',
                    'second' => 'password123',
                ],
            ]
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $userServiceMock = $this->createMock(UserServiceInterface::class);
        $translatorMock = $this->createMock(TranslatorInterface::class);

        $formMock = $this->createMock(FormInterface::class);
        $formViewMock = $this->createMock(FormView::class);

        $controller = $this->getMockBuilder(RegistrationController::class)
            ->setConstructorArgs([$userServiceMock, $translatorMock])
            ->onlyMethods(['createForm', 'addFlash', 'redirectToRoute']) // 'render' is not called on valid form
            ->getMock();

        $controller->expects($this->once())
            ->method('createForm')
            ->with(RegistrationType::class, $this->isInstanceOf(User::class), ['method' => Request::METHOD_POST])
            ->willReturn($formMock);

        $formMock->expects($this->once())->method('handleRequest')->with($request);
        $formMock->expects($this->once())->method('isSubmitted')->willReturn(true);
        $formMock->expects($this->once())->method('isValid')->willReturn(true);

        $userServiceMock->expects($this->once())->method('save')->with($this->isInstanceOf(User::class));

        $translatorMock->expects($this->once())
            ->method('trans')
            ->with('message.registered_successfully')
            ->willReturn('Registered successfully');

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('success', 'Registered successfully');

        $redirectResponse = new RedirectResponse('/login');
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('app_login')
            ->willReturn($redirectResponse);

        $response = $controller->register($request);

        $this->assertSame($redirectResponse, $response);
    }

    public function testRegisterWithInvalidForm(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $userServiceMock = $this->createMock(UserServiceInterface::class);
        $translatorMock = $this->createMock(TranslatorInterface::class);

        $formMock = $this->createMock(FormInterface::class);
        $formViewMock = $this->createMock(FormView::class);

        $controller = $this->getMockBuilder(RegistrationController::class)
            ->setConstructorArgs([$userServiceMock, $translatorMock])
            ->onlyMethods(['createForm', 'render']) // 'addFlash', 'redirectToRoute' are not called on invalid form
            ->getMock();

        $controller->expects($this->once())
            ->method('createForm')
            ->with(RegistrationType::class, $this->isInstanceOf(User::class), ['method' => Request::METHOD_POST])
            ->willReturn($formMock);

        $formMock->expects($this->once())->method('handleRequest')->with($request);
        $formMock->expects($this->once())->method('isSubmitted')->willReturn(true);
        $formMock->expects($this->once())->method('isValid')->willReturn(false);
        $formMock->expects($this->once())->method('createView')->willReturn($formViewMock);

        $controller->expects($this->once())
            ->method('render')
            ->with('registration/index.html.twig', ['form' => $formViewMock])
            ->willReturn(new Response('Form with errors'));

        $response = $controller->register($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('Form with errors', $response->getContent());
    }
}