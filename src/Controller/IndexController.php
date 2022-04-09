<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(ArticleRepository $articleRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $LIMIT_PER_PAGE = 6;

        $pagination = $paginator->paginate(
            $articleRepository->getQueryForAllArticles(),
            $request->query->getInt('page', 1),
            $LIMIT_PER_PAGE
        );
        $pagination->setTemplate('@KnpPaginator/Pagination/bootstrap_v5_pagination.html.twig');


        return $this->render('index/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
