<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("adminDashboard/articles")
 */
class ArticlesController extends AbstractController
{
    /**
     * @Route("/", name="articles_index", methods={"GET"})
     */
    public function index(ArticlesRepository $articlesRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articlesRepository->findAll(),
        ]);
    }


    /**
     * @Route("/new", name="articles_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);




        if ($form->isSubmitted() && $form->isValid()) {
            $img = $article->getThumbnailImg();

            if($img)
            {
                $newfn = $img->getClientOriginalName();
                $fs = new Filesystem();
                if($fs->exists($this->getParameter('bibliotheque').'/'.$img)){
                    $article->setThumbnailImg($newfn);
                }
                else{
                    try {
                        $img->move(
                            $this->getParameter('bibliotheque'),
                            $newfn
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    $article->setThumbnailImg($newfn);
                }
            }
            $article->setSlug(str_replace(' ','-',$article->getTitle()));
            $article->setCreatedAt(new \DateTime());
            $article->setViews(0);
            $article->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('articles_index');
        }

        return $this->render('articles/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="articles_show", methods={"GET"})
     */
    public function show(Articles $article): Response
    {
        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="articles_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Articles $article): Response
    {
        $lastImg = $article->getThumbnailImg();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $img = $article->getThumbnailImg();
            if($img)
            {
                $newfn = $img->getClientOriginalName();
                try {
                    $img->move(
                        $this->getParameter('bibliotheque'),
                        $newfn
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $article->setThumbnailImg($newfn);
            }else{
                $article->setThumbnailImg($lastImg);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('articles_index');
        }

        return $this->render('articles/edit.html.twig', [
            'article' => $article,
            'lastImg'=>$lastImg,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="articles_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Articles $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('articles_index');
    }
}
