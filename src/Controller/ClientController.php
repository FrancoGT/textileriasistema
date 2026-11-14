<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Legacy\MaintenanceFormState;
use App\Maintenance\ClientData;
use App\Maintenance\ClientService;
use App\Maintenance\ClientType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly ClientService $clients,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1030.php', name: 'app_clients', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'clients');
        $options = $this->clients->options($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        // Only the type selection screen has a button named "Salir"; the list screens return to it.
        if ('Salir' === $input->value('Salir')) {
            return new RedirectResponse(self::MENU);
        }
        $type = ClientType::tryFrom((string) $form->recall('type'));
        $data = ['scBehavior' => '0', 'saTipCli' => $options['types'], 'scTipCli' => $type?->value ?? '',
            'saEstado' => $options['statuses'], 'saPriori' => $options['priorities'], 'saCIIU' => $options['ciiu'],
            'saCodA' => [], 'saCodB' => [], 'saCodC' => [], 'scTipo' => $type?->value ?? '', 'scCodCli' => '',
            'scNombre' => '', 'scNroDNI' => '', 'scNroRUC' => '', 'scDirec' => '', 'scTelef' => '',
            'scEstado' => '', 'scCIIU' => '', 'scSiglas' => '', 'scPriori' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            switch ($input->action) {
                case 'Aceptar':
                    $form->close();
                    $type = ClientType::tryFrom($input->value('pcTipCli')) ?? throw new \DomainException('Seleccione un tipo de persona.');
                    $form->remember('type', $type->value);
                    $data = $this->listing($data, $user, $type);
                    break;
                case 'Cancelar':
                    $form->close();
                    $data = null === $type ? $data : $this->listing($data, $user, $type);
                    break;
                case 'Nuevo':
                    $type ?? throw new \DomainException('Seleccione un tipo de persona.');
                    $form->open(null);
                    $data['scBehavior'] = '1';
                    break;
                case 'Aplicar':
                    $form->close();
                    $row = $this->clients->find($user, $input->value('pcCodCli'));
                    $form->open($row['code']);
                    $form->remember('type', $row['type']);
                    $data = array_replace($data, ['scBehavior' => '6', 'scTipo' => $row['type'], 'scCodCli' => $row['code'],
                        'scNombre' => $row['name'], 'scNroDNI' => $row['dni'], 'scNroRUC' => $row['ruc'], 'scDirec' => $row['address'],
                        'scTelef' => $row['phone'], 'scEstado' => $row['status'], 'scCIIU' => $row['ciiu'],
                        'scSiglas' => $row['acronym'], 'scPriori' => $row['priority']]);
                    break;
                case 'Grabar':
                    $code = $form->openedCode($input->value('pcCodCli'));
                    $type ?? throw new \DomainException('Seleccione un tipo de persona.');
                    $data = array_replace($data, ['scBehavior' => null === $code ? '1' : '6', 'scCodCli' => $code ?? '',
                        'scNombre' => $input->value('pcNombre'), 'scNroDNI' => $input->value('pcNroDNI'), 'scNroRUC' => $input->value('pcNroRUC'),
                        'scDirec' => $input->value('pcDirec'), 'scTelef' => $input->value('pcTelef'), 'scEstado' => $input->value('pcEstado'),
                        'scCIIU' => $input->value('pcCIIU'), 'scSiglas' => $input->value('pcSiglas'), 'scPriori' => $input->value('pcPriori')]);
                    $this->clients->save($user, $code, new ClientData($type, $data['scNombre'], $data['scNroDNI'], $data['scNroRUC'],
                        $data['scDirec'], $data['scTelef'], $data['scEstado'], $data['scCIIU'], $data['scSiglas'], $data['scPriori']));
                    $form->close();
                    $form->flash('Grabación conforme.');

                    return new RedirectResponse('/ERP1030.php', 303);
                default:
                    $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1030.tpl', $data, $status);
    }

    private function listing(array $data, string $user, ClientType $type): array
    {
        return array_replace($data, ['scBehavior' => $type->listBehavior(), 'scTipo' => $type->value, 'scTipCli' => $type->value,
            'saCod'.$type->value => $this->clients->choices($user, $type)]);
    }
}
