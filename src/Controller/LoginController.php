<?php

declare(strict_types=1);

namespace App\Controller;

use App\Session\ErpSession;
use App\Security\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Receives the login form. MenuController shows the menu on a GET of /Mnu0000.php.
 */
final class LoginController extends AbstractController
{
    private const MENU = 'Mnu0000.php?Id=0000';
    private const PASSWORD_CHANGE = 'Mnu1000.php';

    public function __construct(private readonly LoginService $login, private readonly ErpSession $session)
    {
    }

    #[Route('/Mnu0000.php', name: 'app_login', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $async = 'xmlhttprequest' === strtolower((string) $request->headers->get('X-Requested-With'));
        $code = $request->request->all()['pcCodigo'] ?? null;
        $password = $request->request->all()['pcClave'] ?? null;
        if (!\is_string($code) || !\is_string($password) || '' === $code || '' === $password
            || \strlen($code) > 40 || \strlen($password) > 80) {
            return $this->failure($async, 'Ingrese su código y clave.', Response::HTTP_BAD_REQUEST);
        }
        try {
            $user = $this->login->authenticate($code, $password);
        } catch (\DomainException $exception) {
            return $this->failure($async, $exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
        $this->session->login($user['code'], $user['name']);
        $target = $user['must_change'] ? self::PASSWORD_CHANGE : self::MENU;
        if ($async) {
            return $this->noStore(new JsonResponse(['ok' => true, 'redirect' => $target]));
        }

        return new RedirectResponse('/'.$target, Response::HTTP_SEE_OTHER);
    }

    private function failure(bool $async, string $message, int $status): Response
    {
        if ($async) {
            return $this->noStore(new JsonResponse(['ok' => false, 'message' => $message], $status));
        }

        return $this->noStore($this->render('security/login.html.twig', ['error' => $message], new Response('', $status)));
    }

    private function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
