<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewPublicationFormType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Préfixe de la route et du nom de toutes les pages de la partie blog du site
 */
#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{
    /**
     * Contrôleur de la page permettant de créer un nouvel article
     */
    #[Route('/nouvelle-publication/', name: 'publication_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationNew(Request $request, ManagerRegistry $doctrine): Response
    {
        // Création d'un nouvelle article vide
        $newArticle = new Article();

        // Création d'un formulaire de création d'article, lié à l'article vide
        $form = $this->createForm(NewPublicationFormType::class, $newArticle);

        // Liaison des données POST au formaulaire

        $form->handleRequest($request);

        // Si le formulaire a bien été envoyé et sans erreurs
        if($form->isSubmitted() && $form->isValid()){

            // On termine d'hydrater l'article
            $newArticle
                ->setPublicationDate(new \DateTime())
                ->setAuthor($this->getUser())
                ;

            $em = $doctrine->getManager();
            $em->persist($newArticle);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Article publié avec succès !');

            // Rediriger sur la page qui montre le nouvel article
            return $this->redirectToRoute('blog_publication_view', [

                'slug'=> $newArticle->getSlug(),

            ]);


        }


        return $this->render('blog/publication_new.html.twig',[
            'new_publication_form'=> $form->createView(),
        ]);


    }

    /**
     * Contrôleur de la page qui liste tous les articles
     */

    #[Route('/publication/liste/', name: 'publication_list')]

    public function publicationList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        $requestedPage = $request->query->getInt('page', 1);

        if($requestedPage < 1 ){
            throw new NotFoundHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10
        );

        return $this->render('blog/publication_list.html.twig',[

            'articles' => $articles,
        ]);

        // Récupération du repository des articles
        $articleRepo = $doctrine->getRepository(Article::class);

        // On demande au repository de nous donner tous les articles qui sont en BDD
        $articles = $articleRepo->findAll();


        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles,
        ]);

    }

    /*
     *  Contrôleur de la page permettant de voir un article en détail
     */

    #[Route('/publication/{slug}/', name: 'publication_view')]
        public function publicationView(Article $article): Response
    {

        return $this->render('blog/publication_view.html.twig', [
            'article'=> $article,
        ]);
    }

    /**
     * Contrôleur de la page admin servant à supprimer un article via son id passé dans l' URL
     * Accès réservé aux administrateurs (ROLE_ADMIN)
     */
    #[Route('/publication/suppression/{id}', name: 'publication_delete', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationDelete(Article $article, ManagerRegistry $doctrine, Request $request): Response
    {
        // Verif si token csrf valide
        if (!$this->isCsrfTokenValid('blog_publication_delete_' . $article->getId(), $request->query->get('csrf_token'))) {
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        } else {
            $em = $doctrine->getManager();
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'La publication a été supprimée avec succès !');
        }
        return $this->redirectToRoute('blog_publication_list');
    }

    /**
     * Contrôleur de la page admin servant à modifier un article existant via son id passés dans l'URL
     *
     * Accès réservé aux administrateurs (ROLE_ADMIN)
     */

    #[Route('/publication/modifier/{id}', name: 'publication_edit', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationEdit(Article $article, Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(NewPublicationFormType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em = $doctrine->getManager();

            $em->flush();

            $this->addFlash('succes', 'Publication modifiée avec succès !');

            return $this->redirectToRoute('blog_publication_view',[
                'slug' => $article->getSlug(),
            ]);

        }

        return $this->render('blog/publication_edit.html.twig', [
            'edit_form' => $form->createView(),
            ]);
    }

}
