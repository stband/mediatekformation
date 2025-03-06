<?php

namespace App\Controller\admin;

use App\Entity\Formation;
use App\Form\FormationsType;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlleur de la page d'administration des formations
 */
class AdminFormationsController extends AbstractController
{
    private const PAGE_ADMIN_FORMATIONS = 'pages/admin/admin.formations.html.twig';

    /**
     *
     * @var FormationRepository
     */
    private $formationRepository;

    /**
     *
     * @var CategorieRepository
     */
    private $categorieRepository;

    public function __construct(FormationRepository $formationRepository, CategorieRepository $categorieRepository) {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository= $categorieRepository;
    }

    /**
     * Page principale : affichage de toutes les formations
     */
    #[Route('/admin/formations', name: 'admin.formations')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADMIN_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * Trie les formations selon un champ/ordre spécifique, avec support de table relationnelle (playlist, catégorie)
     */
    #[Route('/admin/formations/tri/{champ}/{ordre}/{table}', name: 'admin.formations.sort')]
    public function sort($champ, $ordre, $table=""): Response{
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADMIN_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * Recherche de formations par contenu d’un champ donné (filtrage dynamique)
     */
    #[Route('/admin/formations/recherche/{champ}/{table}', name: 'admin.formations.findallcontain')]
    public function findAllContain($champ, Request $request, $table=""): Response{
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADMIN_FORMATIONS, [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    /**
     * Supprime une formation (et la détache de sa playlist), après vérification
     */
    #[Route('/admin/formations/delete/{id}', name: 'admin.formations.delete', methods: ['GET'])]
    public function delete(int $id): Response
    {
        // On vérifie si la formation existe
        $formation = $this->formationRepository->find($id);

        if (!$formation) {
            $this->addFlash('danger', 'La formation demandée est introuvable.');
            return $this->redirectToRoute('admin.formations');
        }

        // On retire la formation de sa playlist si nécessaire
        if ($formation->getPlaylist()) {
            $formation->setPlaylist(null);
        }

        $this->formationRepository->remove($formation);

        $this->addFlash('success', 'La formation a été supprimée avec succès.');

        return $this->redirectToRoute('admin.formations');
    }

    /**
     * Affiche un formulaire d’édition d’une formation existante
     */
    #[Route('/admin/formations/edit?{id}', name: 'admin.formations.edit')]
    public function edit(int $id, Request $request): Response {
        $formation = $this->formationRepository->find($id);
        $formFormations = $this->createForm(FormationsType::class, $formation);

        $formFormations->handleRequest($request);
        if ($formFormations->isSubmitted() && $formFormations->isValid()) {
            $this->formationRepository->addOrEdit($formation);
            return $this->redirectToRoute('admin.formations');
        }

        return $this->render('pages/admin/admin.formations.edit.html.twig', [
            'formation' => $formation,
            'formformations' => $formFormations->createView()
        ]);
    }

    /**
     * Affiche un formulaire pour ajouter une nouvelle formation
     */
    #[Route('/admin/formations/add', name: 'admin.formations.add')]
    public function addFormation(Request $request): Response{
        $formation = new Formation();
        $formFormation = $this->createForm(FormationsType::class, $formation);

        $formFormation->handleRequest($request);
        if ($formFormation->isSubmitted() && $formFormation->isValid()) {
            $this->formationRepository->addOrEdit($formation);
            return $this->redirectToRoute('admin.formations');
        }

        return $this->render('pages/admin/admin.formations.add.html.twig', [
            'formformations' => $formFormation->createView(),
        ]);
    }
}
