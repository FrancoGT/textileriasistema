<?php

declare(strict_types=1);

namespace App\Controller;

use App\Session\ErpSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Login screen. Opening it ends the current session. The form posts to
 * Mnu0000.php, which LoginController handles.
 */
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    #[Route('/index.php', name: 'app_home_legacy', methods: ['GET'])]
    public function index(ErpSession $session): Response
    {
        $session->logout();

        return $this->render('security/login.html.twig');
    }
}
