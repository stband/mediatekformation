<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminPlaylistsController extends AbstractController
{
    private const PAGE_ADMIN_PLAYLISTS = 'pages/admin/admin.playlists.html.twig';

    #[Route('/admin/playlists', name: 'admin.playlists')]
    public function index(): Response
    {
        return $this->render(self::PAGE_ADMIN_PLAYLISTS);
    }
}