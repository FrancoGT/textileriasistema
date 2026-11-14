<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Legacy\MaintenanceFormState;
use App\Security\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class UserController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly UserService $users,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1010.php', name: 'app_users', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'users');
        $options = $this->users->options($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saCodUsu' => $this->users->all($user), 'saEstado' => $options['statuses'], 'saPerfil' => $options['profiles'],
            'saCodPer' => [], 'scCodUsu' => '', 'scNombre' => '', 'scEstado' => '', 'sdFecFin' => '', 'scPerfil' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            switch ($input->action) {
                case 'Editar':
                    $form->close();
                    $row = $this->users->find($user, $input->value('pcCodUsu'));
                    $form->open($row['code']);
                    $data = array_replace($data, ['scBehavior' => '1', 'scCodUsu' => $row['code'], 'scNombre' => $row['name'],
                        'scEstado' => $row['status'], 'sdFecFin' => $row['end_date'], 'scPerfil' => $row['profile']]);
                    break;
                case 'Nuevo':
                    $form->close();
                    $data = array_replace($data, ['scBehavior' => '2', 'saCodPer' => $this->users->candidates($user)]);
                    $form->open(null);
                    break;
                case 'Actualizar':
                    $code = $form->openedCode($input->value('pcCodUsu')) ?? throw new \DomainException('Seleccione un usuario.');
                    $row = $this->users->find($user, $code);
                    $data = array_replace($data, ['scBehavior' => '1', 'scCodUsu' => $code, 'scNombre' => $row['name'],
                        'scEstado' => $input->value('pcEstado'), 'sdFecFin' => $input->value('pdFecFin'), 'scPerfil' => $input->value('pcPerfil')]);
                    $this->users->update($user, $code, $input->value('pcEstado'), $input->value('pcPerfil'), $input->value('pdFecFin'));
                    $form->close();
                    $form->flash('Grabación conforme.');

                    return new RedirectResponse('/ERP1010.php', 303);
                case 'Grabar':
                    if (null !== $form->openedCode('')) {
                        throw new \DomainException('Pulse Nuevo antes de grabar.');
                    }
                    $data = array_replace($data, ['scBehavior' => '2', 'scPerfil' => $input->value('pcPerfil'), 'saCodPer' => $this->users->candidates($user)]);
                    $code = $this->users->create($user, $input->value('pcCodPer'), $input->value('pcPerfil'));
                    $form->close();
                    $form->flash('Usuario '.$code.' creado. La clave inicial es el DNI y se cambia en el primer ingreso.');

                    return new RedirectResponse('/ERP1010.php', 303);
                default:
                    $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1010.tpl', $data, $status);
    }
}
