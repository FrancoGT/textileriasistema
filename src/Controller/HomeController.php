<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(Request $request): Response
    {
        // Mismo comportamiento que el index.php original: cerrar la sesion actual.
        // Se usa la sesion nativa de PHP, compartida con el codigo legacy (php_bridge).
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }
        unset($_SESSION['gcNombre'], $_SESSION['gcCodUsu'], $_SESSION['gcTipo'], $_SESSION['login_change_password']);

        return $this->render('security/login.html.twig');
    }
}
