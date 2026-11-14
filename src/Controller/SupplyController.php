<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Legacy\MaintenanceFormState;
use App\Maintenance\SupplyData;
use App\Maintenance\SupplyService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class SupplyController
{
    private const MENU = '/Mnu0000.php?Id=0000';

    public function __construct(
        private readonly SupplyService $supplies,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1050.php', name: 'app_supplies', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'supplies');
        $options = $this->supplies->options($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saData' => $this->supplies->all($user), 'saSubTip' => $options['subtypes'], 'saUniMed' => $options['units'],
            'scCodIns' => '', 'scSubTip' => '', 'scDescri' => '', 'scUniMed' => '', 'snCanGra' => '', 'snPrec' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            switch ($input->action) {
                case 'Nuevo':
                    $form->open(null);
                    $data['scBehavior'] = '1';
                    break;
                case 'Editar':
                    $form->close();
                    $row = $this->supplies->find($user, $input->value('pcCodArs'));
                    $form->open($row['code']);
                    $data = array_replace($data, ['scBehavior' => '1', 'scCodIns' => $row['code'], 'scSubTip' => substr($row['code'], 0, 2),
                        'scDescri' => $row['description'], 'scUniMed' => $row['unit'], 'snCanGra' => $row['granularity'], 'snPrec' => $row['price']]);
                    break;
                case 'Eliminar':
                    $form->close();
                    $this->supplies->delete($user, $input->value('pcCodArs'));
                    $form->flash('Insumo eliminado.');

                    return new RedirectResponse('/ERP1050.php', 303);
                case 'Grabar':
                    $code = $form->openedCode($input->value('pcCodIns'));
                    $data = array_replace($data, ['scBehavior' => '1', 'scCodIns' => $code ?? '',
                        'scSubTip' => null === $code ? $input->value('pcSubTip') : substr($code, 0, 2),
                        'scDescri' => $input->value('pcDescri'), 'scUniMed' => $input->value('pcUniMed'),
                        'snCanGra' => $input->value('pnCanGra'), 'snPrec' => $input->value('pnPrecio')]);
                    if (null !== $code) {
                        $data['scDescri'] = $this->supplies->find($user, $code)['description'];
                    }
                    $this->supplies->save($user, $code, new SupplyData($data['scSubTip'], $data['scDescri'], $data['scUniMed'], $data['snPrec'], $data['snCanGra']));
                    $form->close();
                    $form->flash('Insumo grabado.');

                    return new RedirectResponse('/ERP1050.php', 303);
                default:
                    $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1050.tpl', $data, $status);
    }
}
