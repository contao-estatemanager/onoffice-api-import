<?php

declare(strict_types=1);

/*
 * This file is part of the Contao EstateManager extension "onOffice API Import".
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/onoffice-api-import
 * @copyright Copyright (c) 2021 Oveleon (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 * @author    Daniele Sciannimanica (https://github.com/doishub)
 */

namespace ContaoEstateManager\OnOfficeApiImport\EventListener;

use Contao\CoreBundle\Event\MenuEvent;
use ContaoEstateManager\OnOfficeApiImport\Controller\BackendModule\BackendModuleController;
use ContaoEstateManager\OnOfficeApiImport\EstateManager\AddonManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="contao.backend_menu_build", priority=-255)
 */
class BackendMenuListener
{
    protected $router;
    protected $translator;
    protected $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    public function __invoke(MenuEvent $event): void
    {
        if (!AddonManager::valid())
        {
            return;
        }

        $factory = $event->getFactory();
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName())
        {
            return;
        }

        $contentNode = $tree->getChild('onoffice');

        $node = $factory
            ->createItem('onoffice-import')
            ->setUri($this->router->generate(BackendModuleController::class))
            ->setLabel($this->translator->trans('MOD.onoffice_import.0', [], 'contao_default'))
            ->setLinkAttribute('title', $this->translator->trans('MOD.onoffice_import.1', [], 'contao_default'))
            ->setLinkAttribute('class', 'navigation onoffice_import')
            ->setCurrent(BackendModuleController::class === $this->requestStack->getCurrentRequest()->get('_controller'))
        ;

        $contentNode->addChild($node);
    }
}
