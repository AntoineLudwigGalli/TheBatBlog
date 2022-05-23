<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Form\NewArticleFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController {
    /**
     * Contrôleur de la page permettant de créer un nouvel article
     * Accès réservé aux administrateurs (ROLE_ADMIN)
     */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[isGranted('ROLE_ADMIN')]
    public function newPublication(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response {
        $article = new Article();

        $form = $this->createForm(NewArticleFormType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article
                ->setPublicationDate(new \DateTime())
                ->setAuthor($this->getUser())
                ->setSlug($slugger->slug($article->getTitle())->lower());

            $em = $doctrine->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Article publié avec succès');

            return $this->redirectToRoute('blog_publication_view',
                ['id' => $article->getId(), 'slug' => $article->getSlug(),]);
        }

        return $this->render('blog/new_publication.html.twig', ['form' => $form->createView(),


        ]);
    }

    /**
     * Contrôleur de la page permettant de voir un article en détail (via ID et slug dans l'URL)
     */
    #[Route('/publication/{id}/{slug}/', name: 'publication_view')]
    #[ParamConverter('article', options: ['mapping' => ['id' => 'id', 'slug' => 'slug']])]
    public function publicationView(Article $article, Request $request, ManagerRegistry $doctrine): Response {
if(!$this->getUser()){
    return $this->render('blog/publication_view.html.twig', [
        'article' => $article,
    ]);
}
        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment
                ->setPublicationDate(new \DateTime())
                ->setAuthor($this->getUser())
                ->setArticle($article);
            ;

            $em = $doctrine->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Commentaire publié avec succès');

            unset($comment);
            unset($form);

            $comment = new Comment;
            $form = $this->createForm(CommentFormType::class, $comment);
        }
        return $this->render('blog/publication_view.html.twig', [
            'article' => $article,
            'form' => $form->createView()
            ]);
    }

    /**
     *
     */
    #[Route('/publications/liste', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine, Request $request,
        PaginatorInterface $paginator): Response {

        $requestedPage = $request->query->getInt('page', 1);

        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');
        $articles = $paginator->paginate($query, //Requête créée juste avant
            $requestedPage, // Page qu'on souhaite voir
            8, // Nombre d'articles à afficher par page
        );

        return $this->render('blog/publication_list.html.twig', ['articles' => $articles,]);
    }

    /**
     * Contrôleur de la page admin via son id dans l'url
     *
     * Accès réservé aux admins
     */
    #[Route("/publications/suppression/{id}/", name: 'publication_delete', priority: 10)]
    #[isGranted("ROLE_ADMIN")]
    public function publicationDelete(Article $article, Request $request, ManagerRegistry $doctrine): Response {
        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('blog_publication_delete_' . $article->getId(), $csrfToken)) {
            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer');
        } else {

            $em = $doctrine->getManager();
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'Article supprimé avec succès');
        }

        return $this->redirectToRoute('blog_publication_list');
    }

    /**
     * Contrôleur de la page admin via son id dans l'url pour modifier un article
     *
     * Accès réservé aux admins
     */
    #[Route("/publications/modifier/{id}/", name: 'publication_edit', priority: 10)]
    #[isGranted("ROLE_ADMIN")]
    public function publicationEdit(Article $article, Request $request, ManagerRegistry $doctrine,
        SluggerInterface $slugger): Response {

        $form = $this->createForm(NewArticleFormType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setSlug($slugger->slug($article->getTitle())->lower());

            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Article supprimé avec succès');
            return $this->redirectToRoute('blog_publication_view',
                ['id' => $article->getId(), 'slug' => $article->getSlug(),]);
        }

        return $this->render('blog/publication_edit.html.twig', ['form' => $form->createView(),]);
    }

    #[Route('/commentaire/suppression/{id}/', name: "comment_delete")]
    #[IsGranted('ROLE_ADMIN')]
    public function commentDelete(Comment $comment, Request $request, ManagerRegistry $doctrine): Response{
        if(!$this->isCsrfTokenValid('blog_comment_delete_' . $comment->getId(), $request->query->get("csrf_token"))){
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        } else {

            $em = $doctrine->getManager();
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé avec succès');
        }
        return $this->redirectToRoute('blog_publication_view', [
           'id'=>$comment->getArticle()->getId(),
           'slug'=>$comment->getArticle()->getSlug()
        ]);

    }
}