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
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;
use Oveleon\ContaoOnofficeApiBundle\OnOfficeRead;

class ObjectTypeImport
{
    private OnOfficeRead $onOfficeHandler;

    private int $intCount = 0;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->onOfficeHandler = new OnOfficeRead();
    }

    /**
     * Fetch all object types and prepare database.
     */
    public function fetch(?array $attributes = null): array
    {
        $arrData = $this->onOfficeHandler->run('fields', null, null, $attributes, true);
        $arrObjectTypes = $arrData['data']['records'][0]['elements']['objektart']['permittedvalues'] ?? null;

        return [
            'message' => 'Please wait, the data will be imported',
            'countAbsolute' => \count($arrObjectTypes ?? []),
            'simulateProgress' => 30,
            'task' => [
                'action' => '/onoffice/import/objectTypes',
                'data' => $arrObjectTypes,
            ],
        ];
    }

    /**
     * Import object types.
     *
     * @param $arrRecords
     */
    public function import($arrRecords): array
    {
        if (null === $arrRecords)
        {
            return [];
        }

        foreach ($arrRecords as $vid => $title)
        {
            $objType = new ObjectTypeModel();

            $objType->title = $title;
            $objType->vid = $vid;
            $objType->tstamp = time();
            $objType->published = 1;

            $objType->save();

            ++$this->intCount;
        }

        return [
            'message' => 'Import successful',
            'count' => $this->intCount,
        ];
    }

    /**
     * Truncate table.
     */
    public function truncate(): void
    {
        $strTable = ObjectTypeModel::getTable();
        Database::getInstance()->execute("TRUNCATE TABLE $strTable");
    }
}
