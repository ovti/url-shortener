<?php
/**
 * Url controller.
 */


namespace App\Controller;

use App\Entity\Url;
use App\Service\UrlServiceInterface;
use App\Form\Type\UrlType;
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


    #[Route(name: 'url_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->urlService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render(
            'url/index.html.twig',
            ['pagination' => $pagination]
        );

    }

    #[Route(
        '/{id}',
        name: 'url_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Url $url): Response
    {
        return $this->render(
            'url/show.html.twig',
            ['url' => $url]
        );
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
        $url = new Url();
        $form = $this->createForm(UrlType::class, $url);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->urlService->save($url);
            $this->addFlash('success', 'message_created_successfully');

            return $this->redirectToRoute('url_index');
        }
        return $this->render(
            'url/create.html.twig',
            ['form' => $form->createView()]
        );

    }




}
