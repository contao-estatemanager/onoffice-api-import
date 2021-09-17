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

$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = ['ContaoEstateManager\OnOfficeApiImport\EstateManager', 'AddonManager'];

use ContaoEstateManager\OnOfficeApiImport\Model\OnOfficeImportModel;

if (ContaoEstateManager\OnOfficeApiImport\EstateManager\AddonManager::valid())
{
    // Models
    $GLOBALS['TL_MODELS']['tl_onoffice_import'] = OnOfficeImportModel::class;
}
