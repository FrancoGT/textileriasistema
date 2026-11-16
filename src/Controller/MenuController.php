<?php

declare(strict_types=1);

namespace App\Controller;

use App\Session\ErpSession;
use App\View\ErpView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MenuController
{
    public function __construct(private readonly ErpSession $session, private readonly ErpView $views)
    {
    }

    #[Route('/Mnu0000.php', name: 'app_menu', methods: ['GET'])]
    public function __invoke(): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }

        return $this->views->render('Mnu0000', ['scCodigo' => $user, 'scNombre' => (string) $this->session->get('gcNombre')]);
    }
}
