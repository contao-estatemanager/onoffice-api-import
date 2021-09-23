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

namespace ContaoEstateManager\OnOfficeApiImport\Mapper;

use Symfony\Component\PropertyAccess\PropertyAccess;

class SearchCriteriaMapper
{
    /**
     * Record id
     */
    private ?int $id;

    /**
     * Record
     */
    private ?array $record;

    /**
     * Schema
     */
    private ?int $schema;

    /**
     * Object types
     */
    private ?array $bag;

    /**
     * Schemas
     */
    public const SCHEMA_FLAT = 1;
    public const SCHEMA_RANGE = 2;

    /**
     * Create Mapper Instance
     */
    public function __construct(?array $record=null)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        if($record)
        {
            $this->setRecord($record);
        }
    }

    /**
     * Set new record
     */
    public function setRecord(array $record): void
    {
        if(array_key_exists('id', $record))
        {
            $this->id = $record['id'];
        }

        if(array_key_exists('elements', $record))
        {
            $record = $record['elements'];
        }

        $this->record = $record;
    }

    /**
     * Return current record
     */
    public function getRecord(): ?array
    {
        return $this->record;
    }

    /**
     * Return current record id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set mapping schema
     */
    public function setSchema(int $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * Set bag
     */
    public function setBag(?array $bag): void
    {
        if(null === $bag)
        {
            return;
        }

        $this->bag = $bag;
    }

    /**
     * Return bag
     */
    public function getBag(): ?array
    {
        return $this->bag;
    }

    /**
     * Check if mapper has data
     */
    public function hasData(): bool
    {
        return $this->record !== null;
    }

    /**
     * Executes the mapping and return the new structure
     */
    public function apply($allowEmpty=false): ?array
    {
        if(!$this->schema)
        {
            trigger_error('No schema declaired', E_USER_ERROR);
        }

        $mapping = [];

        foreach ($this->getStructure() as $field => $map)
        {
            if(($value = $this->getValue($map)) || $allowEmpty)
            {
                $mapping[$field] = $value;
            }
        }

        return $mapping;
    }

    /**
     * Return structure map by current schema
     */
    private function getStructure(): ?array
    {
        $structure = null;

        switch ($this->schema)
        {
            case self::SCHEMA_FLAT:
                $structure = [
                    'marketingType' => '[vermarktungsart]',
                    'room_from' => '[anzahl_zimmer__von]',
                    'room_to' => '[anzahl_zimmer__bis]',
                    'area_from' => '[wohnflaeche__von]',
                    'area_to' => '[wohnflaeche__bis]',
                    'city' => '[range_ort]',
                    'country' => '[range_land]',
                    'range' => '[range]',
                    'latitude' => '[range_breitengrad]',
                    'longitude' => '[range_laengengrad]',
                    'postalcode' => ['serialize', ['[range_plz]']],
                    'price_from' => ['condition', [
                        ['[kaufpreis__von]', '[kaltmiete__von]'],
                        ['[vermarktungsart]', 'kauf']
                    ]],
                    'price_to' => ['condition', [
                        ['[kaufpreis__bis]', '[kaltmiete__bis]'],
                        ['[vermarktungsart]', 'kauf']
                    ]],
                    'objectType' => ['keyInBag', ['[objektart]']],
                ];
                break;

            case self::SCHEMA_RANGE:
                $structure = [
                    'marketingType' => '[vermarktungsart][0]',
                    'room_from' => '[range_anzahl_zimmer][0]',
                    'room_to' => '[range_anzahl_zimmer][1]',
                    'city' => '[Umkreis][range_ort]',
                    'country' => '[Umkreis][range_land]',
                    'range' => '[Umkreis][range]',
                    'latitude' => '[Umkreis][range_breitengrad]',
                    'longitude' => '[Umkreis][range_laengengrad]',
                    'postalcode' => ['serialize', ['[Umkreis][range_plz]']],
                    'price_from' => ['condition', [
                        ['[range_kaufpreis][0]', '[range_kaltmiete][0]'],
                        ['[vermarktungsart][0]', 'kauf']
                    ]],
                    'price_to' => ['condition', [
                        ['[range_kaufpreis][1]', '[range_kaltmiete][1]'],
                        ['[vermarktungsart][0]', 'kauf']
                    ]],
                    'objectType' => ['keyInBag', ['[objektart][0]']],
                ];
                break;

        }

        return $structure;
    }

    /**
     * Return value by structure map
     */
    private function getValue($map): ?string
    {
        if (\is_string($map))
        {
            return $this->propertyAccessor->getValue($this->record, $map);
        }

        if(\is_array($map))
        {
            return call_user_func_array("self::" . $map[0], (array) $map[1]);
        }

        return null;
    }

    /**
     * Value function: Serialize value
     */
    private function serialize($field): ?string
    {
        $value = $this->propertyAccessor->getValue($this->record, $field);

        if($value)
        {
            return serialize((array) $value);
        }

        return null;
    }

    /**
     * Value function: Get value by condition
     */
    private function condition(array $fields, array $condition): ?string
    {
        $conditionValue = $this->propertyAccessor->getValue($this->record, $condition[0]);

        if($conditionValue === $condition[1])
        {
            return $this->propertyAccessor->getValue($this->record, $fields[0]);
        }

        return $this->propertyAccessor->getValue($this->record, $fields[1]);
    }

    /**
     * Value function: Check matching key in bag
     */
    private function keyInBag($field): ?string
    {
        $value = $this->propertyAccessor->getValue($this->record, $field);

        if (\array_key_exists($value, $this->bag))
        {
            return $this->bag[$value];
        }

        return $value;
    }
}
