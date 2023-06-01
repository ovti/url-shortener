<?php
/*
 * User controller.
 */
namespace App\Controller;

use App\Entity\User;
use App\Service\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use App\Form\Type\UserPasswordType;
use App\Form\Type\UserEmailType;
use App\Form\Type\UserRoleType;

/**
 * Class UserController.
 *
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private userServiceInterface $userService;
    private Security $security;

    public function __construct(UserServiceInterface $userService, Security $security)
    {
        $this->userService = $userService;
        $this->security = $security;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     *
     * @Route(
     *     name="user_index",
     *     methods={"GET"},
     * )
     */
    public function index(Request $request): Response
    {
        $user = $this->security->getUser();
        $pagination = $this->userService->getPaginatedList($request->query->getInt('page', 1));
        return $this->render(
            'user/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/{id}",
     *     name="user_show",
     *     methods={"GET"},
     *     requirements={"id": "[1-9]\d*"},
     * )
     */
    #[IsGranted('VIEW', subject: 'user')]
    public function show(User $user): Response
    {
        return $this->render(
            'user/show.html.twig',
            ['user' => $user]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/{id}/edit/password",
     *     name="user_edit",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     * )
     */
    #[IsGranted('EDIT_USER_DATA', subject: 'user')]
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(UserPasswordType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $this->userService->save($user);
            $this->addFlash('success', 'message.updated_successfully');
            return $this->redirectToRoute('app_homepage');
        }
        return $this->render(
            'user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Edit email action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/{id}/edit/email",
     *     name="user_edit_email",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     )
     */
    #[IsGranted('EDIT_USER_DATA', subject: 'user')]
    public function editEmail(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEmailType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);
            $this->addFlash('success', 'message.updated_successfully');
            return $this->redirectToRoute('app_homepage');
        }
        return $this->render(
            'user/edit_email.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Edit role.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/{id}/edit/role",
     *     name="user_edit_role",
     *     methods={"GET", "PUT"},
     *     requirements={"id": "[1-9]\d*"},
     *     )
     *
     */
    #[IsGranted('ROLE_ADMIN')]
    public function editRole(Request $request, User $user): Response
    {
        $form = $this->createForm(UserRoleType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);
            $this->addFlash('success', 'message.updated_successfully');
            return $this->redirectToRoute('app_homepage');
        }
        return $this->render(
            'user/edit_role.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
}