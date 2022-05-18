<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewArticleFormType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog', name: 'blog_')]
class BlogController
    extends
    AbstractController
{
    /**
     * Contrôleur de la page permettant de créer un nouvel article
     * Accès réservé aux administrateurs (ROLE_ADMIN)
     */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[isGranted('ROLE_ADMIN')]
    public function newPublication(Request          $request,
                                   ManagerRegistry  $doctrine,
                                   SluggerInterface $slugger): Response
    {
        $article =
            new Article();

        $form =
            $this->createForm(NewArticleFormType::class,
                $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() &&
            $form->isValid()) {
            $article
                ->setPublicationDate(new \DateTime())
                ->setAuthor($this->getUser())
                ->setSlug($slugger->slug($article->getTitle())
                    ->lower());

            $em =
                $doctrine->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('success','Article publié avec succès');

            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->render('blog/new_publication.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }

    /**
     * Contrôleur de la page permettant de voir un article en détail (via ID et slug dans l'URL)
     */
    #[Route('/publication/{id}/{slug}/', name: 'publication_view')]
    #[ParamConverter('article', options: ['mapping' => ['id' => 'id',
        'slug' => 'slug']])]
    public function publicationView(Article $article): Response
    {
        return $this->render('blog/publication_view.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * Contrôleur de la page qui liste les articles
     */
    #[Route('/publications/liste', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine): Response{

        $articleRepo = $doctrine->getRepository(Article::class);

        $articles = $articleRepo->findAll();

        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles,
        ]);
    }
}
