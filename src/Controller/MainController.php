<?php

namespace App\Controller;

use App\Form\EditPhotoFormType;
use Doctrine\Persistence\ManagerRegistry;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    /**
     * Contrôleur de la page d'accueil
     */
    #[Route('/', name: 'main_home')]
    public function home(): Response
    {
        return $this->render('main/home.html.twig');
    }

    /*
     * Contrôleur de la page profil
     *
     * Accès réservé aux personnes connectées (ROLE_USER)
     */

    #[Route('/mon-profil/', name: 'main_profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(): Response
    {
        return $this->render('main/profil.html.twig');
    }

    /*
     * Contrôleur de la page de mofication de la photo de profil
     *
     * Accès réservé aux utilisateurs connectés (ROLE_USER)
     */

    #[Route('/changer-photo-de-profil/', name: 'main_edit_photo')]
    #[IsGranted('ROLE_USER')]

    public function editPhoto(Request $request, ManagerRegistry $doctrine, CacheManager $cacheManager): Response
    {

        $form = $this->createForm(EditPhotoFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $photo = $form->get('photo')->getData();
            $connectedUser = $this->getUser();
            $photoLocation = $this->getParameter('app.user.photo.directory');

            $newFileName = 'user' . $connectedUser->getId() . '.' . $photo->guessExtension();

            if($connectedUser->getPhoto() != null && file_exists($photoLocation . $connectedUser->getPhoto())){
                $cacheManager->remove('images/profils/' . $connectedUser->getPhoto() );
                unlink($photoLocation . $connectedUser->getPhoto() );

            }

            $em = $doctrine->getManager();
            $connectedUser->setPhoto($newFileName);
            $em->flush();


            dump($photo);


            $photo->move(
               $photoLocation,
                $newFileName,
            );

            // Message flash + redirection
            $this->addFlash('success', 'Photo de profil modifiée avec succès !');
            return $this->redirectToRoute('main_profil');
        }


        return $this->render('main/edit_photo.html.twig', [
            'edit_photo_form' => $form->createView(),
        ]);
    }

}
