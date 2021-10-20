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

use Contao\Message;
use Contao\System;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

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

    private $modules = [];
    private $sections = [];

    private $defaultSettings = [];
    private $bundles = [];

    private $showMessage = false;

    public function __construct(Environment $twig, TranslatorInterface $translator)
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

        // Get installed bundles
        $this->bundles = System::getContainer()->getParameter('kernel.bundles');

        // Set default setting fields
        $this->defaultSettings = [
            'truncate' => [
                'label' => [
                    $this->translator->trans('onoffice_import.settings.truncate.0', [], 'contao_default'),
                    $this->translator->trans('onoffice_import.settings.truncate.1', [], 'contao_default'),
                ],
                'inputType' => 'checkbox',
                'required' => false,
            ],
        ];

        // Create modules
        $this->addRegions();
        $this->addObjectTypes();
        $this->addSearchCriteria();

        // Check module message
        if($this->showMessage)
        {
            Message::addInfo('Einige Funktionen stehen nicht zur Verfügung, da die entsprechenden Erweiterungen nicht installiert sind.');
        }

        // Render template
        return new Response($this->twig->render(
            '@EstateManagerOnOfficeApiImport/be_onoffice_import.html.twig',
            [
                'title' => $this->translator->trans('onoffice_import.title', [], 'contao_default'),
                'message' => Message::generate(),
                'texts' => [
                    'retrieve' => $this->translator->trans('onoffice_import.retrieve_data', [], 'contao_default'),
                    'import' => $this->translator->trans('onoffice_import.button_import', [], 'contao_default'),
                    'settings' => $this->translator->trans('onoffice_import.button_settings', [], 'contao_default'),
                    'confirm' => $this->translator->trans('onoffice_import.confirm_import', [], 'contao_default'),
                ],
                'modules' => $this->modules,
                'sections' => $this->sections,
            ]
        ));
    }

    private function addRegions(): void
    {
        $this->modules[] = [
            'name' => $this->translator->trans('onoffice_import.regions.title', [], 'contao_default'),
            'desc' => $this->translator->trans('onoffice_import.regions.desc', [], 'contao_default'),
            'module' => 'regions',
            'exists' => $this->isAllowed('RegionEntity'),
            'fields' => array_merge(
                [
                    'language' => [
                        'label' => [
                            $this->translator->trans('onoffice_import.settings.language.0', [], 'contao_default'),
                            $this->translator->trans('onoffice_import.settings.language.1', [], 'contao_default'),
                        ],
                        'inputType' => 'text',
                        'required' => true,
                    ],
                ],
                $this->defaultSettings
            ),
        ];
    }

    private function addObjectTypes(): void
    {
        $this->modules[] = [
            'name' => $this->translator->trans('onoffice_import.objectTypes.title', [], 'contao_default'),
            'desc' => $this->translator->trans('onoffice_import.objectTypes.desc', [], 'contao_default'),
            'module' => 'objectTypes',
            'exists' => $this->isAllowed('ObjectTypeEntity'),
            'fields' => array_merge(
                [
                    'language' => [
                        'label' => [
                            $this->translator->trans('onoffice_import.settings.language.0', [], 'contao_default'),
                            $this->translator->trans('onoffice_import.settings.language.1', [], 'contao_default'),
                        ],
                        'inputType' => 'text',
                        'required' => true,
                    ],
                ],
                $this->defaultSettings
            ),
        ];
    }

    private function addSearchCriteria(): void
    {
        $bundleExists = $this->isAllowed('EstateManagerLeadMatchingTool');

        $this->modules[] = [
            'name' => $this->translator->trans('onoffice_import.searchCriteria.title', [], 'contao_default'),
            'desc' => $this->translator->trans('onoffice_import.searchCriteria.desc', [], 'contao_default'),
            'module' => 'searchCriteria',
            'exists' => $bundleExists,
            'fields' => array_merge(
                [
                    'marketingType' => [
                        'label' => [
                            $this->translator->trans('onoffice_import.settings.marketingType.0', [], 'contao_default'),
                            $this->translator->trans('onoffice_import.settings.marketingType.1', [], 'contao_default'),
                        ],
                        'inputType' => 'select',
                        'options' => [
                            '' => '-',
                            'kauf' => $this->translator->trans('onoffice_import.settings.marketingType.buy', [], 'contao_default'),
                            'miete' => $this->translator->trans('onoffice_import.settings.marketingType.rent', [], 'contao_default'),
                        ],
                        'required' => true,
                    ],
                    'regions' => [
                        'label' => [
                            $this->translator->trans('onoffice_import.settings.import_regions.0', [], 'contao_default'),
                            $this->translator->trans('onoffice_import.settings.import_regions.1', [], 'contao_default'),
                        ],
                        'inputType' => 'checkbox',
                        'required' => false,
                    ],
                ],
                $this->defaultSettings
            ),
        ];

        $this->sections['cron'] = [
            'label' => $this->translator->trans('onoffice_import.sections.labelCron', [], 'contao_default'),
            'list' => [
                [
                    'title' => $this->translator->trans('onoffice_import.sections.cronCreateSearchCriteria.0', [], 'contao_default'),
                    'desc' => $this->translator->trans('onoffice_import.sections.cronCreateSearchCriteria.1', [], 'contao_default'),
                    'content' => '/onoffice/create/searchCriteria',
                    'c2a' => '/onoffice/create/searchCriteria',
                    'exists' => $bundleExists,
                ],
                [
                    'title' => $this->translator->trans('onoffice_import.sections.cronUpdateSearchCriteria.0', [], 'contao_default'),
                    'desc' => $this->translator->trans('onoffice_import.sections.cronUpdateSearchCriteria.1', [], 'contao_default'),
                    'content' => '/onoffice/update/searchCriteria',
                    'c2a' => '/onoffice/update/searchCriteria',
                    'exists' => $bundleExists,
                ],
            ],
        ];
    }

    private function isAllowed($bundleName): bool
    {
        $blnAllowed = \array_key_exists($bundleName, $this->bundles);

        if(!$blnAllowed)
        {
            $this->showMessage = true;
        }

        return $blnAllowed;
    }
}
