<?php

namespace App\Controller\admin;

use App\Entity\Formation;
use App\Entity\Playlist;
use App\Form\PlaylistsType;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminPlaylistsController extends AbstractController
{
    private const PAGE_ADMIN_PLAYLISTS = 'pages/admin/admin.playlists.html.twig';

    /**
     *
     * @var PlaylistRepository
     */
    private $playlistRepository;

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

    public function __construct(PlaylistRepository $playlistRepository, FormationRepository $formationRepository, CategorieRepository $categorieRepository) {
        $this->playlistRepository = $playlistRepository;
        $this->formationRepository = $formationRepository;
        $this->categorieRepository= $categorieRepository;
    }

    #[Route('/admin/playlists', name: 'admin.playlists')]
    public function index(): Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADMIN_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }

    /**
     * Route de tri (nom ou nombre de formations)
     */
    #[Route('/admin/playlists/tri/{champ}/{ordre}', name: 'admin.playlists.sort')]
    public function sort($champ, $ordre): Response{
        if ($champ === 'name') {
            $playlists = $this->playlistRepository->findAllOrderByName($ordre);
        } elseif ($champ === 'count') {
            $playlists = $this->playlistRepository->findAllOrderByNbFormations($ordre);
        } else {
            $playlists = $this->playlistRepository->findAll();
        }

        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADMIN_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }

    /**
     * Route de recherche par champ (nom ou catégorie)
     */
    #[Route('/admin/playlists/recherche/{champ}/{table}', name: 'admin.playlists.findallcontain')]
    public function findAllContain($champ, Request $request, $table=""): Response{
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::PAGE_ADMIN_PLAYLISTS, [
            'playlists' => $playlists,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }

    /**
     * Route de suppression d’une playlist (interdite si elle contient des formations)
     */
    #[Route('/admin/playlists/delete?{id}', name: 'admin.playlists.delete')]
    public function delete(int $id): Response {
        $playlist = $this->playlistRepository->find($id);
        if ($playlist->getFormationsCount() > 0)
            return new Response("Cette playlist ne peut être supprimée car elle n'est pas vide.", Response::HTTP_FORBIDDEN);

        $this->playlistRepository->remove($playlist);
        return $this->redirectToRoute('admin.playlists');
    }

    /**
     * Route de modification d’une playlist (formulaire pré-rempli)
     */
    #[Route('/admin/playlists/edit?{id}', name: 'admin.playlists.edit')]
    public function edit(int $id, Request $request): Response {
        $playlist = $this->playlistRepository->find($id);
        $formations = $this->formationRepository->findAllForOnePlaylist($id);
        $categories = $this->categorieRepository->findAll();

        $formPlaylists = $this->createForm(PlaylistsType::class, $playlist);

        $formPlaylists->handleRequest($request);
        if ($formPlaylists->isSubmitted() && $formPlaylists->isValid()) {
            $this->playlistRepository->addOrEdit($playlist);
            return $this->redirectToRoute('admin.playlists');
        }

        return $this->render('pages/admin/admin.playlists.edit.html.twig', [
            'playlist' => $playlist,
            'formplaylists' => $formPlaylists->createView(),
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * Route d’ajout d’une nouvelle playlist
     */
    #[Route('/admin/playlists/add', name: 'admin.playlists.add')]
    public function addFormation(Request $request): Response{
        $playlist = new Playlist();
        $formPlaylists = $this->createForm(PlaylistsType::class, $playlist);

        $formPlaylists->handleRequest($request);
        if ($formPlaylists->isSubmitted() && $formPlaylists->isValid()) {
            $this->playlistRepository->addOrEdit($playlist);
            return $this->redirectToRoute('admin.playlists');
        }

        return $this->render('pages/admin/admin.playlists.add.html.twig', [
            'formplaylists' => $formPlaylists->createView(),
        ]);
    }
}