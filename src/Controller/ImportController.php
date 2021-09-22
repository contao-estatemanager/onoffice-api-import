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

use ContaoEstateManager\OnOfficeApiImport\Import\ObjectTypeImport;
use ContaoEstateManager\OnOfficeApiImport\Import\RegionImport;
use ContaoEstateManager\OnOfficeApiImport\Import\SearchCriteriaImport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = false})
 */
class ImportController
{
    /**
     * Fetch regions from onOffice.
     *
     * @Route("/onoffice/fetch/regions", name="onoffice_fetch_regions")
     */
    public function fetchRegions(Request $request): JsonResponse
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
    public function importRegions(Request $request): JsonResponse
    {
        $regionImporter = new RegionImport();

        $arrRequest = $request->toArray();

        $arrData = $regionImporter->import($arrRequest['data'], $arrRequest['language']);

        return new JsonResponse($arrData);
    }

    /**
     * Fetch object types from onOffice.
     *
     * @Route("/onoffice/fetch/objectTypes", name="onoffice_fetch_objecttypes")
     */
    public function fetchObjectTypes(Request $request): JsonResponse
    {
        $objectTypeImporter = new ObjectTypeImport();

        if ($request->get('truncate') ?? false)
        {
            $objectTypeImporter->truncate();
        }

        $arrData = $objectTypeImporter->fetch([
            'language' => $request->get('language'),
            'labels' => true,
            'modules' => ['estate'],
        ]);

        return new JsonResponse($arrData);
    }

    /**
     * Import regions.
     *
     * @Route("/onoffice/import/objectTypes", name="onoffice_import_objecttypes")
     */
    public function importObjectTypes(Request $request): JsonResponse
    {
        $regionImporter = new ObjectTypeImport();

        $arrRequest = $request->toArray();

        $arrData = $regionImporter->import($arrRequest['data']);

        return new JsonResponse($arrData);
    }

    /**
     * Fetch search criteria from onOffice.
     *
     * @Route("/onoffice/fetch/searchCriteria", name="onoffice_fetch_searchcriteria")
     */
    public function fetchSearchCriteria(Request $request): JsonResponse
    {
        $searchCriteriaImporter = new SearchCriteriaImport();

        if ($request->get('truncate') ?? false)
        {
            $searchCriteriaImporter->truncate();
        }

        $arrData = $searchCriteriaImporter->fetch([
            'offset' => 0,
            'limit' => 0,
            'outputall' => 0,
            'searchdata' => [
                'vermarktungsart' => $request->get('marketingType'),
            ],
        ], (bool) $request->get('regions'));

        return new JsonResponse($arrData);
    }

    /**
     * Import regions.
     *
     * @Route("/onoffice/import/searchCriteria", name="onoffice_import_searchcriteria")
     */
    public function importSearchCriteria(Request $request): JsonResponse
    {
        $searchCriteriaImporter = new SearchCriteriaImport();

        $arrRequest = $request->toArray();

        $arrData = $searchCriteriaImporter->partialImport([
            'offset' => $arrRequest['offset'],
            'limit' => SearchCriteriaImport::LIMIT,
            'outputall' => 1,
            'searchdata' => [
                'vermarktungsart' => $arrRequest['marketingType'],
            ],
        ], (bool) $arrRequest['regions']);

        return new JsonResponse($arrData);
    }
}
