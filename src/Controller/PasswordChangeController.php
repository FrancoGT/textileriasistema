<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\View\ErpView;
use App\Session\MaintenanceFormState;
use App\Security\LoginService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordChangeController
{
    private const MENU = '/Mnu0000.php?Id=0000';

    public function __construct(
        private readonly LoginService $login,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/Mnu1000.php', name: 'app_password_change', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'password');
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        $status = 200;
        $message = '';
        if ('Grabar' === $input->action) {
            try {
                $this->login->changePassword($user, $input->value('pcClave'), $input->value('pcClave1'), $input->value('pcClave2'));

                return new RedirectResponse(self::MENU, Response::HTTP_SEE_OTHER);
            } catch (\DomainException $exception) {
                $message = $exception->getMessage();
                $status = 422;
            }
        }
        $response = $this->views->render('Mnu1000', [
            'scNombre' => (string) $this->session->get('gcNombre'),
            'maintenanceToken' => $form->token(),
            'maintenanceMessage' => $message,
        ], $status);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
