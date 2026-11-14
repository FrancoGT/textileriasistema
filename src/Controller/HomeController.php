<?php

declare(strict_types=1);

namespace App\Controller;

use App\Legacy\LegacySession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pantalla de inicio de sesion, migrada de legacy/index.php + Plantillas/Index.tpl
 * a Symfony + Twig. Primera pantalla migrada (patron a seguir para el resto).
 *
 * El formulario sigue enviando a Mnu0000.php (legacy), que valida al usuario
 * y carga el menu principal.
 */
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    #[Route('/index.php', name: 'app_home_legacy', methods: ['GET'])]
    public function index(LegacySession $session): Response
    {
        $session->logout();

        return $this->render('security/login.html.twig');
    }
}
