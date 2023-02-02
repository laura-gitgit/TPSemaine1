<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recette', name: 'recette')]
class RecetteController extends AbstractController
{
    #[Route('/liste', name: '_liste')]
    public function recettes(RecetteRepository $recetteRepository): Response
    {
        //TODO vérifier cookie, si oui rediriger vers fonction
        /**
         * public function index(Request $request, RecetteRepository $recetteRepository): Response
        {
        $param = $request->cookies->get('tri_recettes', 'nom');

        if($param === 'nom'){
        $recettes = $recetteRepository->findByName(['nom' => $param]);
        }elseif ($param === 'est_favori'){
        $recettes = $recetteRepository->findByEstFavori(['est_favori' => $param]);
        }

        return $this->render('recette/liste.html.twig', compact('recettes'));
        }
         */
        $recettes = $recetteRepository->findAll();
        return $this->render('recette/liste.html.twig', compact('recettes'));
    }

    #[Route('/details/{id}', name: '_details', requirements: ['id'=>'\d+'])]
    public function recette(int $id, RecetteRepository $recetteRepository): Response
    {
        $recette = $recetteRepository->findOneBy(['id'=> $id]);
        return $this->render('recette/details.html.twig', [
            'id' => $id,
            'recette' => $recette
        ]);
    }

    #[Route('/tri/{param}', name: '_tri')]
    public function recettesTri(string $param, RecetteRepository $recetteRepository): Response
    {
        if($param === 'nom'){
            $recettes = $recetteRepository->findByName(['nom' => $param]);
        }elseif ($param === 'est_favori'){
            $recettes = $recetteRepository->findByEstFavori(['est_favori' => $param]);
        }

        //créer cookie pour stocker la valeur de tri
        $cookie = new Cookie('tri_recettes', $param, time()+3600 * 24 * 30);

        //ajouter cookie a la réponse
        $response = new Response();
        $response->headers->setCookie($cookie);

        //envoyer à la vue
        return $response->setContent($this->renderView('recette/liste.html.twig', compact('recettes')));
    }

    #[Route('/{id}/estFavori', name: '_estFavori')]
    public function updateFavori (int $id, RecetteRepository $recetteRepository, EntityManagerInterface $em) : Response
    {
        $objet = $recetteRepository->findOneBy(['id'=> $id]);
        $objet->setEstFavori(!$objet->isEstFavori());

        //modifier la base de donnée
        $em->persist($objet); //persiste en base de données
        $em->flush();

        //le fait de redirigier va faire appel à la méthode d'affichage de la liste
        return $this->redirectToRoute('recette_liste');
    }
}
