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

use ContaoEstateManager\OnOfficeApiImport\Import\SearchCriteriaImport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = false})
 */
class SearchCriteriaController
{
    /**
     * Fetch search criteria from onOffice.
     *
     * @Route("/onoffice/fetch/searchCriteria", name="onoffice_fetch_searchcriteria")
     */
    public function fetch(Request $request): JsonResponse
    {
        $searchCriteriaImporter = new SearchCriteriaImport();

        if ($request->get('truncate') ?? false)
        {
            $searchCriteriaImporter->truncate();
        }

        $arrData = $searchCriteriaImporter->fetchPartialImport([
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
     * Import search criteria.
     *
     * @Route("/onoffice/import/searchCriteria", name="onoffice_import_searchcriteria")
     */
    public function import(Request $request): JsonResponse
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

    /**
     * Update single search criteria.
     *
     * @Route("/onoffice/update/searchCriteria", name="onoffice_update_searchcriteria")
     */
    public function update(): JsonResponse
    {
        $searchCriteriaImporter = new SearchCriteriaImport();
        $searchCriteriaImporter->singleUpdate();

        return new JsonResponse(['ok']);
    }
}
