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
use ContaoEstateManager\RegionEntity\RegionModel;
use Oveleon\ContaoOnofficeApiBundle\OnOfficeRead;

class RegionImport
{
    private OnOfficeRead $onOfficeHandler;

    private $intCount = 0;

    public function __construct()
    {
        $this->onOfficeHandler = new OnOfficeRead();
    }

    /**
     * Fetch all regions and prepare database
     */
    public function prepare($attributes, $truncate = false): array
    {
        if($truncate)
        {
            $this->truncate();
        }

        $arrData = $this->onOfficeHandler->run('regions', null, null, $attributes, true);

        return [
            'message'  => 'Please wait, the data will be imported',
            'meta'     => $arrData['data']['meta'],
            'truncate' => $truncate,
            'task'     => [
                'action' => '/onoffice/import/regions',
                'data' => $arrData['data']['records'],
                'language' => $attributes['language']
            ]
        ];
    }

    public function import($arrRecords, $rootLanguage): array
    {
        if(null === $arrRecords)
        {
            return [];
        }

        $objRoot = RegionModel::findByLanguage($rootLanguage);

        if(null === $objRoot) {
            $objRoot = new RegionModel();
            $objRoot->type = 'root';
            $objRoot->title = $rootLanguage;
            $objRoot->language = $rootLanguage;
            $objRoot->published = 1;
            $objRoot->save();
        }

        $rootId = $objRoot->id;

        foreach ($arrRecords as $arrRecord)
        {
            if(array_key_exists('elements', $arrRecord))
            {
                $arrRecord = $arrRecord['elements'];
            }

            $this->importRegion($arrRecord, $rootId);
        }

        return [
            'message'  => 'Import successful',
            'count'    => $this->intCount
        ];
    }

    public function importRegion($arrRecord, $parentId = null): void
    {
        $objRegion = new RegionModel();
        $objRegion->type = 'regular';
        $objRegion->pid = $parentId;
        $objRegion->title = $arrRecord['name'];
        $objRegion->description = $arrRecord['description'];
        $objRegion->country = $arrRecord['country'];
        $objRegion->state = $arrRecord['state'];
        $objRegion->postalcodes = !empty($arrRecord['postalcodes']) ? serialize($arrRecord['postalcodes']) : null;
        $objRegion->published = 1;

        $objRegion->save();

        $this->intCount++;

        if(!empty($arrRecord['children']))
        {
            foreach ($arrRecord['children'] as $arrChildren)
            {
                $this->importRegion($arrChildren, $objRegion->id);
            }
        }
    }

    public function truncate(): void
    {
        $strTable = RegionModel::getTable();
        Database::getInstance()->execute("TRUNCATE TABLE $strTable");
    }
}
