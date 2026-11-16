<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\View\ErpView;
use App\Session\MaintenanceFormState;
use App\Security\ProfileService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly ProfileService $profiles,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/ERP1020.php', name: 'app_profiles', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'profiles');
        $options = $this->profiles->options($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $profile = (string) $form->recall('profile');
        $data = ['scBehavior' => '0', 'saPerfil' => $options['profiles'], 'scPerfil' => $profile, 'saEstado' => $options['statuses'],
            'saCodOpc' => [], 'scEstado' => '', 'pcDescri' => '', 'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            switch ($input->action) {
                case 'Aplicar':
                    $profile = $input->value('pcPerfil');
                    $data = $this->granted($data, $user, $profile);
                    $form->remember('profile', $profile);
                    break;
                case 'Nuevo':
                    $data = $this->granted($data, $user, $profile);
                    $data = array_replace($data, ['scBehavior' => '2', 'saCodOpc' => $this->profiles->available($user, $profile)]);
                    break;
                case 'Eliminar':
                    $data = $this->granted($data, $user, $profile);
                    $this->profiles->revoke($user, $profile, $input->value('pcCodOpc'));
                    $data = $this->granted($data, $user, $profile);
                    $data['maintenanceMessage'] = 'Opción eliminada del perfil.';
                    break;
                case 'Grabar':
                    $data = $this->granted($data, $user, $profile);
                    $this->profiles->grant($user, $profile, $input->value('pcCodOpc'), $input->value('pcEstado'));
                    $data = $this->granted($data, $user, $profile);
                    $data['maintenanceMessage'] = 'Opción asignada al perfil.';
                    break;
                default:
                    $form->remember('profile', null);
                    $data['scPerfil'] = '';
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('ERP1020', $data, $status);
    }

    private function granted(array $data, string $user, string $profile): array
    {
        $opened = $this->profiles->open($user, $profile);

        return array_replace($data, ['scBehavior' => '1', 'scPerfil' => $profile, 'pcDescri' => $opened['description'], 'saPerfil' => $opened['granted']]);
    }
}
