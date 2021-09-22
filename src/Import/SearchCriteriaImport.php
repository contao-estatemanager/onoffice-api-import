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

namespace ContaoEstateManager\OnOfficeApiImport\Import;

use Contao\Database;
use ContaoEstateManager\LeadMatchingTool\Model\SearchCriteriaModel;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;
use ContaoEstateManager\RegionEntity\Region;
use ContaoEstateManager\RegionEntity\RegionConnectionModel;
use ContaoEstateManager\RegionEntity\RegionModel;
use Oveleon\ContaoOnofficeApiBundle\OnOfficeRead;

class SearchCriteriaImport
{
    /**
     * onOffice Handler.
     */
    private OnOfficeRead $onOfficeHandler;

    /**
     * Current count.
     */
    private int $intCount = 0;

    /**
     * Existing object types.
     */
    private array $arrObjectTypes;

    /**
     * Import steps.
     */
    public const LIMIT = 500;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->onOfficeHandler = new OnOfficeRead();
    }

    /**
     * Fetch all search criteria.
     *
     * @param mixed|null $attributes
     */
    public function fetch(?array $attributes = null, bool $importRegions = false): array
    {
        $arrData = $this->onOfficeHandler->run('search', 'searchcriteria', null, $attributes, true);

        return [
            'message' => 'Import package (0-'.static::LIMIT.')',
            'countAbsolute' => $arrData['data']['meta']['cntabsolute'] ?? 0,
            'task' => [
                'action' => '/onoffice/import/searchCriteria',
                'offset' => 0,
                'marketingType' => $attributes['searchdata']['vermarktungsart'],
                'regions' => $importRegions,
            ],
        ];
    }

    /**
     * Import search criteria.
     *
     * @param $attributes
     */
    public function partialImport($attributes, bool $importRegions = false): array
    {
        $intOffset = $attributes['offset'];

        $arrData = $this->onOfficeHandler->run('search', 'searchcriteria', null, $attributes, true);
        $arrRecords = $arrData['data']['records'] ?? null;

        $objObjectTypes = ObjectTypeModel::findAll();
        $this->arrObjectTypes = [];

        foreach ($objObjectTypes as $objObjectType)
        {
            $this->arrObjectTypes[$objObjectType->vid] = $objObjectType->id;
        }

        if ($arrRecords)
        {
            foreach ($arrRecords as $record)
            {
                $this->import($record, $importRegions);
                ++$this->intCount;
            }
        }

        $intCountAbsolute = $arrData['data']['meta']['cntabsolute'] ?? null;
        $intImportedAbsolute = $intOffset + $this->intCount;

        if ($intCountAbsolute && $intImportedAbsolute >= $intCountAbsolute)
        {
            return [
                'message' => 'Import successful',
                'count' => $intImportedAbsolute,
            ];
        }

        $newOffset = $intOffset + static::LIMIT;
        $nextOffset = $newOffset + static::LIMIT;

        return [
            'message' => 'Import package ('.$newOffset.'-'.$nextOffset.')',
            'count' => $newOffset,
            'task' => [
                'action' => '/onoffice/import/searchCriteria',
                'offset' => $newOffset,
                'marketingType' => $attributes['searchdata']['vermarktungsart'],
                'regions' => $importRegions,
            ],
        ];
    }

    /**
     * Import search criteria record.
     *
     * @param $arrRecord
     */
    public function import($arrRecord, bool $importRegions = false): void
    {
        // Check if record exists
        if (!$objRecord = SearchcriteriaModel::findOneBy('vid', $arrRecord['id']))
        {
            $objRecord = new SearchcriteriaModel();
            $objRecord->vid = $arrRecord['id'];
        }

        $arrData = $arrRecord['elements'];

        $arrMapping = [
            'marketingType' => 'vermarktungsart',
            'room_from' => 'anzahl_zimmer__von',
            'room_to' => 'anzahl_zimmer__bis',
            'area_from' => 'wohnflaeche__von',
            'area_to' => 'wohnflaeche__bis',
            'city' => 'range_ort',
            'country' => 'range_land',
            'range' => 'range',
            'latitude' => 'range_breitengrad',
            'longitude' => 'range_laengengrad',
            'postalcode' => function ($a) {
                return serialize((array) $a['range_plz'] ?? []);
            },
            'price_from' => function ($a) {
                return ('miete' === $a['vermarktungsart'] ? $a['kaltmiete__von'] : $a['kaufpreis__von']) ?? '';
            },
            'price_to' => function ($a) {
                return ('miete' === $a['vermarktungsart'] ? $a['kaltmiete__bis'] : $a['kaufpreis__bis']) ?? '';
            },
            'objectType' => function ($a) {
                if (\array_key_exists($a['objektart'], $this->arrObjectTypes))
                {
                    return $this->arrObjectTypes[$a['objektart']];
                }

                return $a['objektart'];
            },
        ];

        $objRecord->tstamp = time();
        $objRecord->published = 1;

        foreach ($arrMapping as $field => $key)
        {
            if (\is_string($key) && $arrData[$key])
            {
                $objRecord->{$field} = $arrData[$key];
            }
            elseif (\is_callable($key) && $key instanceof \Closure)
            {
                if ($val = $key($arrData))
                {
                    $objRecord->{$field} = $val;
                }
            }
        }

        // Import regions
        if ($importRegions && \is_array($arrData['regionaler_zusatz']))
        {
            $arrRegions = [];
            $currentId = $objRecord->id ?? Database::getInstance()->getNextId(SearchCriteriaModel::getTable());

            foreach ($arrData['regionaler_zusatz'] as $regionKey)
            {
                if ($objRegion = RegionModel::findOneBy('vid', $regionKey))
                {
                    // delete previous connections
                    RegionConnectionModel::deleteByPidAndPtable($currentId, SearchCriteriaModel::getTable());

                    // save new connections
                    Region::saveConnectionRecord($objRegion->id, $currentId, SearchCriteriaModel::getTable());

                    $arrRegions[] = $objRegion->id;
                }
            }

            $objRecord->regions = serialize($arrRegions);
        }

        $objRecord->save();
    }

    /**
     * Truncate table.
     */
    public function truncate(): void
    {
        $strTable = SearchCriteriaModel::getTable();
        Database::getInstance()->execute("TRUNCATE TABLE $strTable");
    }
}
