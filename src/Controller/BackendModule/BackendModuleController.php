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

namespace ContaoEstateManager\OnOfficeApiImport\Controller\BackendModule;

use Contao\System;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

/**
 * @Route("/contao/onoffice-import",
 *     name=BackendModuleController::class,
 *     defaults={"_scope": "backend"}
 * )
 */
class BackendModuleController extends AbstractController
{
    private $twig;
    private $translator;

    public function __construct(TwigEnvironment $twig, TranslatorInterface $translator)
    {
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function __invoke(): Response
    {
        // Load language files
        System::loadLanguageFile('onoffice_import');

        // Load script and style files
        $GLOBALS['TL_CSS'][] = 'bundles/estatemanageronofficeapiimport/styles/backend.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/estatemanageronofficeapiimport/scripts/dist/main.js';

        // Set default setting fields
        $arrDefaultFields = [
            'truncate' => [
                'label' => [
                    $this->translator->trans('onoffice_import.settings.truncate.0', [], 'contao_default'),
                    $this->translator->trans('onoffice_import.settings.truncate.1', [], 'contao_default'),
                ],
                'inputType' => 'checkbox',
                'required' => false
            ]
        ];

        // Create modules
        $bundles = System::getContainer()->getParameter('kernel.bundles');
        $arrModules = [
            [
                'name' => $this->translator->trans('onoffice_import.regions.title', [], 'contao_default'),
                'desc' => $this->translator->trans('onoffice_import.regions.desc', [], 'contao_default'),
                'module' => 'regions',
                'exists' => \array_key_exists('RegionEntity', $bundles),
                'fields' => array_merge(
                    [
                        'language' => [
                            'label' => [
                                $this->translator->trans('onoffice_import.settings.language.0', [], 'contao_default'),
                                $this->translator->trans('onoffice_import.settings.language.1', [], 'contao_default'),
                            ],
                            'inputType' => 'text',
                            'required' => true
                        ]
                    ],
                    $arrDefaultFields
                )
            ],
            [
                'name' => $this->translator->trans('onoffice_import.objectTypes.title', [], 'contao_default'),
                'desc' => $this->translator->trans('onoffice_import.objectTypes.desc', [], 'contao_default'),
                'module' => 'objectTypes',
                'exists' => \array_key_exists('ObjectTypeEntity', $bundles),
                'fields' => null
            ],
            [
                'name' => $this->translator->trans('onoffice_import.searchCriteria.title', [], 'contao_default'),
                'desc' => $this->translator->trans('onoffice_import.searchCriteria.desc', [], 'contao_default'),
                'module' => 'searchCriteria',
                'exists' => \array_key_exists('EstateManagerLeadMatchingTool', $bundles),
                'fields' => null
            ],
        ];

        return new Response($this->twig->render(
            '@EstateManagerOnOfficeApiImport/be_onoffice_import.html.twig',
            [
                'title' => $this->translator->trans('onoffice_import.title', [], 'contao_default'),
                'texts' => [
                    'import'   => $this->translator->trans('onoffice_import.button_import', [], 'contao_default'),
                    'settings' => $this->translator->trans('onoffice_import.button_settings', [], 'contao_default'),
                    'confirm'  => $this->translator->trans('onoffice_import.confirm_import', [], 'contao_default')
                ],
                'modules' => $arrModules,
            ]
        ));
    }
}
