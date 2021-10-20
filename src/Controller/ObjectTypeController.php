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

namespace ContaoEstateManager\OnOfficeApiImport\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use ContaoEstateManager\OnOfficeApiImport\Import\ObjectTypeImport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = false})
 */
class ObjectTypeController
{
    private $importer;

    public function __construct(ContaoFramework $framework)
    {
        $framework->initialize();

        $this->importer = new ObjectTypeImport();
    }

    /**
     * Fetch object types from onOffice.
     *
     * @Route("/onoffice/fetch/objectTypes", name="onoffice_fetch_objecttypes")
     */
    public function fetch(Request $request): JsonResponse
    {
        if ($request->get('truncate') ?? false)
        {
            $this->importer->truncate();
        }

        $arrData = $this->importer->fetch([
            'language' => $request->get('language'),
            'labels' => true,
            'modules' => ['estate'],
        ]);

        return new JsonResponse($arrData);
    }

    /**
     * Import object types.
     *
     * @Route("/onoffice/import/objectTypes", name="onoffice_import_objecttypes")
     */
    public function import(Request $request): JsonResponse
    {
        $arrRequest = $request->toArray();

        $arrData = $this->importer->import($arrRequest['data']);

        return new JsonResponse($arrData);
    }
}
