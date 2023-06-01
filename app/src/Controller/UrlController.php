<?php
/**
 * Url controller.
 */


namespace App\Controller;

use App\Entity\Url;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\UrlServiceInterface;
use App\Form\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlController
 * @package App\Controller
 */
#[Route('/url')]
class UrlController extends AbstractController
{
    /**
     * Url service.
     */
    private UrlServiceInterface $urlService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * UrlController constructor.
     * @param UrlServiceInterface $urlService
     * @param TranslatorInterface $translator
     */
    public function __construct(UrlServiceInterface $urlService, TranslatorInterface $translator) {
        $this->urlService = $urlService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'url_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);
        /** @var User $user */
        $user = $this->getUser();
        $pagination = $this->urlService->getPaginatedList(
            $request->query->getInt('page', 1),
            $user,
            $filters
        );

        return $this->render('url/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * URL List action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/list',
        name: 'url_list',
        methods: 'GET'
    )]
    public function list(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $pagination = $this->urlService->getPaginatedListForEveryUser(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('url/url_list.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{tag_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['tag_id'] = $request->query->getInt('filters_tag_id');

        return $filters;
    }

    /**
     * Show action.
     *
     * @param Url $url Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'url_show', requirements: ['id' => '[1-9]\d*'], methods: 'GET', )]
//    #[IsGranted('VIEW', subject: 'url')]
    public function show(Url $url): Response
    {
        return $this->render('url/show.html.twig', ['url' => $url]);
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'url_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $url = new Url();
        $url->setUsers($user);
        $form = $this->createForm(
            UrlType::class,
            $url,
            ['action' => $this->generateUrl('url_create')]
        );
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->urlService->save($url);
            $this->addFlash('success', 'message_created_successfully');

            return $this->redirectToRoute('app_homepage');
        }
        return $this->render(
            'url/create.html.twig',
            ['form' => $form->createView()]
        );

    }
    /**
     * Edit action.
     *
     * @param Request  $request  HTTP request
     * @param Url $url Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'url_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'url')]
    public function edit(Request $request, Url $url): Response
    {
        $form = $this->createForm(
            UrlType::class,
            $url,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'url_edit',
                    ['id' => $url->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->save($url);
            $this->addFlash('success', 'message_updated_successfully');

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/edit.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }
    /**
     * Delete action.
     *
     * @param Request  $request  HTTP request
     * @param Url $url Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'url_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'url')]
    public function delete(Request $request, Url $url): Response
    {
        $form = $this->createForm(
            FormType::class,
            $url,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'url_delete',
                    ['id' => $url->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->delete($url);
            $this->addFlash('success', 'message_deleted_successfully');

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/delete.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }
}
