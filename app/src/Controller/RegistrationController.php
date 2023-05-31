<?php
/**
 * Registration controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Service\UserServiceInterface;
use App\Form\Type\RegistrationType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{

    /**
     * User service.
     */
    private UserServiceInterface $userService;

    /**
     * RegistrationController constructor.
     *
     * @param \App\Service\UserServiceInterface $userService User service
     */
    public function __construct(UserServiceInterface $userService) {
        $this->userService = $userService;
    }

    /**
     * Register action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        path: '/register',
        name: 'app_register',
        methods: ['GET', 'POST'],
    )]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);

            $this->addFlash('success', 'message_registered_successfully');

            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'registration/index.html.twig',
            ['form' => $form->createView()]
        );
    }


}