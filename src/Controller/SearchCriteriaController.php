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
use ContaoEstateManager\OnOfficeApiImport\Import\SearchCriteriaImport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "backend", "_token_check" = false})
 */
class SearchCriteriaController
{
    private SearchCriteriaImport $importer;

    public function __construct(ContaoFramework $framework)
    {
        $framework->initialize();

        $this->importer = new SearchCriteriaImport();
    }

    /**
     * Fetch search criteria from onOffice.
     *
     * @Route("/onoffice/fetch/searchCriteria", name="onoffice_fetch_searchcriteria")
     */
    public function fetchPartialImport(Request $request): JsonResponse
    {
        if ($request->get('truncate') ?? false)
        {
            $this->importer->truncate();
        }

        $arrData = $this->importer->fetchPartialImport([
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
    public function partialImport(Request $request): JsonResponse
    {
        $arrData = $this->importer->partialImport([
            'offset' => $request->get('offset'),
            'limit' => SearchCriteriaImport::LIMIT,
            'outputall' => 1,
            'searchdata' => [
                'vermarktungsart' => $request->get('marketingType'),
            ],
        ], (bool) $request->get('regions'));

        return new JsonResponse($arrData);
    }

    /**
     * Update single search criteria.
     *
     * @Route("/onoffice/update/searchCriteria", name="onoffice_update_searchcriteria")
     */
    public function singleUpdate(): JsonResponse
    {
        $this->importer->singleUpdate();

        return new JsonResponse(['ok']);
    }

    /**
     * Update single search criteria.
     *
     * @Route("/onoffice/create/searchCriteria", name="onoffice_create_searchcriteria")
     */
    public function singleCreate(): JsonResponse
    {
        $this->importer->singleCreate();

        return new JsonResponse(['ok']);
    }
}
