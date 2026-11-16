<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\MaintenanceInput;
use App\Http\MaintenanceInputResolver;
use App\Session\ErpSession;
use App\View\ErpView;
use App\Session\MaintenanceFormState;
use App\Maintenance\ProductService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController
{
    private const MENU = '/Mnu0000.php?Id=1000';

    public function __construct(
        private readonly ProductService $products,
        private readonly ErpSession $session,
        private readonly ErpView $views,
    ) {
    }

    #[Route('/ERP1070.php', name: 'app_products', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, #[MapRequestPayload(resolver: MaintenanceInputResolver::class)] MaintenanceInput $input): Response
    {
        $user = $this->session->user();
        if ('' === $user) {
            return new RedirectResponse('/index.php');
        }
        $form = new MaintenanceFormState($this->session, 'products');
        $statuses = $this->products->statuses($user);
        if ($request->isMethod('POST') && !$form->isValidToken($input->value('_token'))) {
            throw new AccessDeniedHttpException('El formulario ha vencido. Recargue la página.');
        }
        if ('Salir' === $input->action) {
            return new RedirectResponse(self::MENU);
        }
        $data = ['scBehavior' => '0', 'saDataP' => $this->products->all($user), 'saEstado' => $statuses, 'saCodIns' => [], 'saData' => [],
            'scCodProd' => '', 'scDescri' => '', 'scEstado' => '', 'scCodIns' => '', 'flagn' => '',
            'maintenanceToken' => $form->token(), 'maintenanceMessage' => $form->takeFlash()];
        $status = 200;
        try {
            // Boton2 belongs to the new product form; Boton1 to the recipe form.
            $newProductForm = '' !== $input->value('Boton2');
            switch ($input->action) {
                case 'Nuevo':
                    $form->open(null);
                    $data['scBehavior'] = '2';
                    break;
                case 'Detalle':
                    $form->close();
                    $product = $this->products->find($user, $input->value('pcCodProd'));
                    $form->open($product['code']);
                    $form->remember('recipe', json_encode($product['recipe'], JSON_THROW_ON_ERROR));
                    $data = $this->recipe($data, $user, $product['code'], $product['description'], $product['recipe']);
                    break;
                case 'Agregar':
                case 'Eliminar':
                case 'Grabar':
                    if ($newProductForm) {
                        if (null !== $form->openedCode('')) {
                            throw new \DomainException('Pulse Nuevo antes de grabar.');
                        }
                        $data = array_replace($data, ['scBehavior' => '2', 'scDescri' => $input->value('pcDescri'), 'scEstado' => $input->value('pcEstado')]);
                        $this->products->create($user, $input->value('pcDescri'), $input->value('pcEstado'));
                        $form->close();
                        $form->flash('Producto grabado.');

                        return new RedirectResponse('/ERP1070.php', 303);
                    }
                    $code = $form->openedCode($input->value('pcCodProd')) ?? throw new \DomainException('Seleccione un producto.');
                    $recipe = json_decode((string) $form->recall('recipe'), true) ?: [];
                    $supply = $input->value('pcCodIns1');
                    if ('Agregar' === $input->action && isset($this->products->supplies($user)[$supply]) && !\in_array($supply, $recipe, true)) {
                        $recipe[] = $supply;
                        $data['flagn'] = '1';
                    }
                    if ('Eliminar' === $input->action) {
                        $recipe = array_values(array_diff($recipe, $input->list('paCheck')));
                    }
                    $form->remember('recipe', json_encode($recipe, JSON_THROW_ON_ERROR));
                    $data = $this->recipe($data, $user, $code, $input->value('pcDescri'), $recipe);
                    if ('Grabar' === $input->action) {
                        $this->products->saveRecipe($user, $code, $input->value('pcDescri'), $recipe);
                        $form->close();
                        $form->flash('Grabación conforme.');

                        return new RedirectResponse('/ERP1070.php', 303);
                    }
                    break;
                default:
                    $form->close();
            }
        } catch (\DomainException $exception) {
            $data['maintenanceMessage'] = $exception->getMessage();
            $status = 422;
        }

        return $this->views->render('ERP1070', $data, $status);
    }

    /** @param list<string> $recipe */
    private function recipe(array $data, string $user, string $code, string $description, array $recipe): array
    {
        $supplies = $this->products->supplies($user);

        return array_replace($data, ['scBehavior' => '1', 'scCodProd' => $code, 'scDescri' => $description,
            'saCodIns' => array_map(static fn (array $row): array => [$row[0], $row[1]], array_values($supplies)),
            'saData' => array_values(array_map(static fn (string $supply): array => $supplies[$supply] ?? [$supply, '', '', ''], $recipe))]);
    }
}
