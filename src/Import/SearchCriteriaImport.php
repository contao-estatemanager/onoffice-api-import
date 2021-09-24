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
use ContaoEstateManager\OnOfficeApiImport\Mapper\SearchCriteriaMapper;
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
     * Search criteria mapper.
     */
    private SearchCriteriaMapper $mapper;

    /**
     * Current count.
     */
    private int $intCount = 0;

    /**
     * Import steps.
     */
    public const LIMIT = 500;

    /**
     * Construct.
     */
    public function __construct()
    {
        // Create mapper
        $this->mapper = new SearchCriteriaMapper();

        // Create onOffice handler
        $this->onOfficeHandler = new OnOfficeRead();
    }

    /**
     * Get all search criteria from onOffice by given attributes.
     */
    public function getAllSearchCriteria($attributes)
    {
        // Set mapper schema
        $this->mapper->setSchema(SearchCriteriaMapper::SCHEMA_FLAT);

        // Return onOffice records
        return $this->onOfficeHandler->run('search', 'searchcriteria', null, $attributes, true);
    }

    /**
     * Return search criteria by their id.
     */
    public function getSearchCriteriaById($id)
    {
        // Set mapper schema
        $this->mapper->setSchema(SearchCriteriaMapper::SCHEMA_RANGE);

        // Return onOffice record
        return $this->onOfficeHandler->run('searchcriterias', null, null, ['mode' => 'searchcriteria', 'ids' => (array) $id], true);
    }

    /**
     * Fetch all search criteria.
     */
    public function fetchPartialImport(?array $attributes = null, bool $importRegions = false): array
    {
        $arrData = $this->getAllSearchCriteria($attributes);

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
     */
    public function partialImport($attributes, bool $importRegions = false): array
    {
        $intOffset = $attributes['offset'];

        $arrData = $this->getAllSearchCriteria($attributes);
        $arrRecords = $arrData['data']['records'] ?? null;

        // Set mapper bag
        $this->mapper->setBag($this->createObjectTypeBag());

        if ($arrRecords)
        {
            foreach ($arrRecords as $record)
            {
                // Set new record
                $this->mapper->setRecord($record);

                // Import record
                $this->import($importRegions);

                // Count imported records
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
     * Update a single search criteria record or delete if not exists anymore.
     */
    public function singleUpdate(): void
    {
        // Fetch the oldest record
        $objSearchCriteria = SearchCriteriaModel::findOneBy(['vid!=?'], [''], [
            'order' => 'tstamp DESC',
        ]);

        if (null === $objSearchCriteria)
        {
            return;
        }

        // Fetch record data from onOffice
        $arrSearchCriteria = $this->getSearchCriteriaById($objSearchCriteria->vid);

        // Update
        if ($record = ($arrSearchCriteria['data']['records'][0] ?? null))
        {
            // Set mapper bag
            $this->mapper->setBag($this->createObjectTypeBag());

            // Set new record
            $this->mapper->setRecord($record);

            // Update record
            $this->import();
        }
        // Delete
        else
        {
            $objSearchCriteria->delete();
        }
    }

    /**
     * Creates a single search criteria.
     */
    public function singleCreate(): void
    {
        $strTable = SearchCriteriaModel::getTable();

        // Fetch latest search criteria
        $objSearchCriteria = Database::getInstance()->execute("SELECT MAX(vid) as vid FROM $strTable");

        if (null === $objSearchCriteria)
        {
            return;
        }

        $intLatestId = (int) $objSearchCriteria->vid;
        $intConnections = 3;

        // Set mapper bag
        $this->mapper->setBag($this->createObjectTypeBag());

        for ($i = 0; $i < $intConnections; ++$i)
        {
            // Set next id
            ++$intLatestId;

            // Check if a record exists with the next id
            $arrSearchCriteria = $this->getSearchCriteriaById($intLatestId);

            if ($record = ($arrSearchCriteria['data']['records'][0] ?? null))
            {
                // Set new record
                $this->mapper->setRecord($record);

                // Update record
                $this->import();

                break;
            }
        }
    }

    /**
     * Import search criteria record.
     */
    private function import(bool $importRegions = false): void
    {
        // Get current record
        $record = $this->mapper->getRecord();

        // Check if record exists
        if (!$objRecord = SearchcriteriaModel::findOneBy('vid', $this->mapper->getId()))
        {
            $objRecord = new SearchcriteriaModel();
            $objRecord->vid = $this->mapper->getId();
        }

        // Set mapped fields
        foreach ($this->mapper->apply() as $field => $value)
        {
            $objRecord->{$field} = $value;
        }

        // Set default fields
        $objRecord->tstamp = time();
        $objRecord->published = 1;

        // Set regions (will only affect by partial import)
        if ($importRegions && \is_array($record['regionaler_zusatz']))
        {
            $arrRegions = [];
            $currentId = $objRecord->id ?? Database::getInstance()->getNextId(SearchCriteriaModel::getTable());

            foreach ($record['regionaler_zusatz'] as $regionKey)
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
     * Creates a new bag of object types.
     */
    private function createObjectTypeBag(): ?array
    {
        $objectTypes = ObjectTypeModel::findAll();

        if (null === $objectTypes)
        {
            return null;
        }

        $return = [];

        foreach ($objectTypes as $objectType)
        {
            $return[$objectType->vid] = $objectType->id;
        }

        return $return;
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
