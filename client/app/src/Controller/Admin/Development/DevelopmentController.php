<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller\Admin\Development;

use OPG\Digideps\Frontend\Controller\AbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/dev')]
final class DevelopmentController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route(path: '/design-system/overview', name: 'designSystemOverview')]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_SUPER_ADMIN')"))]
    #[Template('@App/Admin/Development/designSystem.html.twig')]
    public function designSystemOverview(): array
    {
        return [];
    }
}
