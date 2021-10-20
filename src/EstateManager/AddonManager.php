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

namespace ContaoEstateManager\OnOfficeApiImport\EstateManager;

use Contao\Config;
use Contao\Environment;
use ContaoEstateManager\EstateManager;

class AddonManager
{
    /**
     * Bundle name.
     */
    public static $bundle = 'EstateManagerOnOfficeApiImport';

    /**
     * Package.
     */
    public static $package = 'contao-estatemanager/onoffice-api-import';

    /**
     * Addon config key.
     */
    public static $key = 'addon_onoffice_api_import_license';

    /**
     * Is initialized.
     */
    public static $initialized = false;

    /**
     * Is valid.
     */
    public static $valid = false;

    /**
     * Licenses.
     */
    private static $licenses = [
        'ca404a0f6089696267c3d12b55278ab4',
        'cdc712287a121b98cc99db67fb9bc700',
        'a9f71e816e72160166f8b6cd0c2a2479',
        '0ecf15349697b3f795ac205d50e633d9',
        '3af671bffb9c3fc922012b832856d529',
        '755f3ec00d560e7f72c0965d020b1adb',
        '9c4b07d2c9ccbe3b03c9028f961f06f7',
        '0c5622f6496a06e617936ebc1846220c',
        'ebb64f62fb628aa4c55fda167f8ae1de',
        '2bf7d5a8b7f02db31e4cf8f5fff1e798',
        '8df61c7dd1fe4edec97f1b0a34f5ba30',
        'b41a0052aff928368641ed39d1be4775',
        '7175d4f408a6ffb33d2be9cc61d6fd85',
        '55608c0a5ca3ab846edcedef4d04d317',
        'f18fffc96b63c72e7b65682cd63a847a',
        '834819cc032d63de985a6bc514b46347',
        '4302a864dfdc021f6e5bcb982e1568bf',
        '675a347ef2deb04e61def980d63a667e',
        'cefd7e204d930fca6f1aef69c0ff1084',
        '31ea898c8d2a6762d77fced7daf3f457',
        '5c151d155748abb556132bb2f46831e9',
        'eedc501a16a7117c7f72648d012d3080',
        'f06b4f90b5a42e9f414cf738bbdba823',
        'ed7b91f5eedc550e13e9ef96c200834c',
        'bb4518ad66e28c04171ae233f1124281',
        'f298e32f1369c634f396d21e964123e4',
        'f43ac0964d3dbd7f74063d69ad525f8f',
        '5fbdd5ae863f5bde11e3fe1aa58eeaac',
        'e511c8488745463e570f1459bae18842',
        'fdd5a46cbb9daaf95a174c9a108df134',
        '682c7e9ce4fd2045740a8ac6d3881009',
        'dcd07f980196854243015740b1fe8b5d',
        '6857d844cfd32486e8725ec8705e28e7',
        '8dbe9c2a2aacbfdcfcb7857c24baebb8',
        '3981b6a306ef560588d8f36eb25f9cc4',
        '78d0c656361bc4821e11579caeeb395a',
        'a5c14a79145bf97c1f078c393108b34b',
        '0822502fd3abc48917e06ab0459277ac',
        '3d38ba4a9d0e7e0ab81cf484b756a063',
        '26e7b76eba2750e5d9bcce493bd5c14a',
        '6004aeb7403172efd9d4e17b790a7ed5',
        '227ce178d763301404273e329ffa3411',
        '0d3f9a94c09e967a1343eb2550dfb09c',
        'fd1739e18c9a8bded5ed8f389e08b852',
        'a42a0e47897be74d510130f7f81e6dc6',
        'efc474525cad17f390b65aaac47c2ad8',
        'f91da32f15ae2bd6833e08ff6ddab50e',
        'cb2b2f44fedd7fbdd2dcd21b96372610',
        'cb63522568c510a12698d0ace6888f45',
        '74e0ad277c55bee70650fce77da0ba74',
    ];

    public static function getLicenses(): array
    {
        return static::$licenses;
    }

    public static function valid(): bool
    {
        if (false !== strpos(Environment::get('requestUri'), '/contao/install'))
        {
            return true;
        }

        if (false === static::$initialized)
        {
            static::$valid = EstateManager::checkLicenses(Config::get(static::$key), static::$licenses, static::$key);
            static::$initialized = true;
        }

        return static::$valid;
    }
}
