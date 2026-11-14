<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Legacy\LegacySession;
use App\Legacy\LegacyTemplateRenderer;
use App\Legacy\MaintenanceFormState;
use App\Maintenance\AccountOwner;
use App\Maintenance\BankAccountService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class BankAccountController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly BankAccountService $accounts,
        private readonly LegacySession $session,
        private readonly LegacyTemplateRenderer $views,
    ) {
    }

    #[Route('/ERP1094.php', name: 'app_bank_accounts', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $this->accounts->assertGranted($user);
        $form = new MaintenanceFormState($this->session, 'bank_accounts');
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'scTipo' => '', 'saCodPrv' => [], 'saCodBan' => [], 'scCodPrv' => '', 'scCodBan' => '', 'scNroCta' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            if ('Nuevo' === $input->action) {
                $owner = AccountOwner::tryFrom($input->value('pcTipo')) ?? throw new \DomainException('Seleccione el tipo de cuenta.');
                $form->remember('owner', $owner->value);
                $data = $this->form($data, $user, $owner);
            } elseif ('Grabar' === $input->action) {
                $owner = AccountOwner::tryFrom((string) $form->recall('owner')) ?? throw new \DomainException('Seleccione el tipo de cuenta.');
                $code = $input->value($owner->field());
                $data = $this->form($data, $user, $owner);
                $data = array_replace($data, ['scCodPrv' => $input->value('pcCodPrv'), 'scCodBan' => $input->value('pcCodBan'), 'scNroCta' => $input->value('pcNroCta')]);
                $this->accounts->add($user, $owner, $code, $input->value('pcNroCta'));
                $form->remember('owner', null);
                $form->flash('Grabación conforme.');

                return new RedirectResponse('/ERP1094.php', 303);
            } else {
                $form->remember('owner', null);
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('Plantillas/ERP1094.tpl', $data, $status);
    }

    private function form(array $data, string $user, AccountOwner $owner): array
    {
        $key = AccountOwner::Supplier === $owner ? 'saCodPrv' : 'saCodBan';

        return array_replace($data, ['scBehavior' => '1', 'scTipo' => $owner->value, $key => $this->accounts->owners($user, $owner)]);
    }
}
