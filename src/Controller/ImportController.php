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
class ImportController
{
    /**
     * Fetch regions from onOffice
     *
     * @Route("/onoffice/fetch/regions", name="onoffice_fetch_regions")
     */
    public function fetchRegions(Request $request): JsonResponse
    {
        $regionImporter = new RegionImport();

        $arrOptions = [
            'language' => $request->get('language')
        ];

        $arrData = $regionImporter->prepare($arrOptions, $request->get('truncate') ?? false);

        return new JsonResponse($arrData);
    }

    /**
     * Import regions
     *
     * @Route("/onoffice/import/regions", name="onoffice_import_regions")
     */
    public function importRegions(Request $request): JsonResponse
    {
        $regionImporter = new RegionImport();

        $arrData = $request->toArray();
        $arrData = $regionImporter->import($arrData['data'], $arrData['language']);

        return new JsonResponse($arrData);
    }
}
