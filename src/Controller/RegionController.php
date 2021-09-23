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

use ContaoEstateManager\OnOfficeApiImport\Import\RegionImport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = false})
 */
class RegionController
{
    /**
     * Fetch regions from onOffice.
     *
     * @Route("/onoffice/fetch/regions", name="onoffice_fetch_regions")
     */
    public function fetch(Request $request): JsonResponse
    {
        $regionImporter = new RegionImport();

        if ($request->get('truncate') ?? false)
        {
            $regionImporter->truncate();
        }

        $arrData = $regionImporter->fetch([
            'language' => $request->get('language'),
        ]);

        return new JsonResponse($arrData);
    }

    /**
     * Import regions.
     *
     * @Route("/onoffice/import/regions", name="onoffice_import_regions")
     */
    public function import(Request $request): JsonResponse
    {
        $regionImporter = new RegionImport();

        $arrRequest = $request->toArray();

        $arrData = $regionImporter->import($arrRequest['data'], $arrRequest['language']);

        return new JsonResponse($arrData);
    }
}
