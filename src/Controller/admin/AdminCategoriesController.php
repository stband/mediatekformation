<?php

namespace App\Controller\admin;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminCategoriesController extends AbstractController
{
    private const PAGE_ADMIN_CATEGORIES = 'pages/admin/admin.categories.html.twig';

    /**
     *
     * @var CategorieRepository
     */
    private $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository) {
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * Affiche toutes les catégories (vue principale)
     */
    #[Route('/admin/categories', name: 'admin.categories')]
    public function index(): Response
    {
        $categories = $this->categorieRepository->findAll();

        return $this->render(self::PAGE_ADMIN_CATEGORIES, [
            'categories' => $categories
        ]);
    }

    /**
     * Trie les catégories par nom (ordre croissant ou décroissant)
     * @param string $ordre 'ASC' ou 'DESC'
     */
    #[Route('/admin/categories/tri/{ordre}', name: 'admin.categories.sort')]
    public function sort($ordre): Response{
        $categories = $this->categorieRepository->findAllOrderBy($ordre);
        return $this->render(self::PAGE_ADMIN_CATEGORIES, [
            'categories' => $categories
        ]);
    }

    /**
     * Recherche les catégories contenant une chaîne dans leur nom
     */
    #[Route('/admin/categories/recherche', name: 'admin.categories.findbyname')]
    public function findbyname(Request $request): Response{
        $valeur = $request->get("recherche");
        $categories = $this->categorieRepository->findByName($valeur);
        return $this->render(self::PAGE_ADMIN_CATEGORIES, [
            'categories' => $categories,
            'valeur' => $valeur,
        ]);
    }

    /**
     * Supprime une catégorie si elle n'est liée à aucune formation
     */
    #[Route('/admin/categories/delete?{id}', name: 'admin.categories.delete')]
    public function delete(int $id): Response {
        $categorie = $this->categorieRepository->find($id);

        // Empêche la suppression si la catégorie est liée à des formations
        if (count($categorie->getFormations()) > 0) {
            $this->addFlash('categories_error', "Impossible de supprimer la catégorie « {$categorie->getName()} » car elle est liée à des formations.")
;
            return $this->redirectToRoute('admin.categories');
        }

        $this->categorieRepository->remove($categorie);
        $this->addFlash('categories_success', "La catégorie « {$categorie->getName()} » a été supprimée avec succès.");
        return $this->redirectToRoute('admin.categories');
    }

    /**
     * Ajoute une nouvelle catégorie si le nom n'existe pas déjà
     */
    #[Route('/admin/categories/add', name: 'admin.categories.add')]
    public function addCategorie(Request $request): Response{
        $name = $request->get("name");

        // Vérifie si le champ est vide
        if (empty($name)) {
            $this->addFlash('categories_error', "Le nom de la catégorie ne peut pas être vide.");
            return $this->redirectToRoute('admin.categories');
        }

        // Vérifie si la catégorie existe déjà
        if ($this->categorieRepository->existsByName($name)) {
            $this->addFlash('categories_error', "La catégorie « $name » existe déjà.");
            return $this->redirectToRoute('admin.categories');
        }
        
        $categorie = new Categorie();
        $categorie->setName($name);
        $this->categorieRepository->add($categorie);

        $this->addFlash('categories_success', "La catégorie « $name » a été ajoutée avec succès.");
        return $this->redirectToRoute('admin.categories');
    }
}