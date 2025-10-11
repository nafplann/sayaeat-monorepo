<?php

namespace App\Core;

use App\Enums\InputType;
use App\Enums\RelationshipType;

class AppFieldDef
{
    public string $column;
    public string $label;
    public string $placeholder;

    public bool $browsable;
    public bool $readable;
    public bool $creatable;
    public bool $editable;
    public bool $removable;
    public int $columnOrder;

    public array $validationRulesForAdding;
    public array $validationRulesForEditing;

    public string $datatableClass;
    public InputType $inputType;
    public array $inputAttributes;
    public ?RelationshipType $relationshipType;
    public ?array $selectOptions;

    /**
     * @param String $column
     * @param string|null $label
     * @param string $placeholder
     * @param InputType $inputType
     * @param array $inputAttributes
     * @param array $selectOptions
     * @param RelationshipType|null $relationshipType
     * @param bool $browsable
     * @param bool $readable
     * @param bool $creatable
     * @param bool $editable
     * @param bool $removable
     * @param int $columnOrder
     * @param array $validationRulesForAdding
     * @param array $validationRulesForEditing
     * @param string $datatableClass
     */
    public function __construct(
        string           $column,
        string           $label = null,
        string           $placeholder = '',
        InputType        $inputType = InputType::TEXT,
        array            $inputAttributes = [],
        array            $selectOptions = [],
        RelationshipType $relationshipType = null,
        bool             $browsable = true,
        bool             $readable = true,
        bool             $creatable = true,
        bool             $editable = true,
        bool             $removable = true,
        int              $columnOrder = 1,
        array            $validationRulesForAdding = [],
        array            $validationRulesForEditing = [],
        string           $datatableClass = '',
    )
    {
        $this->column = $column;
        $this->placeholder = $placeholder;
        $this->label = $label ?? $column;
        $this->inputType = $inputType;
        $this->inputAttributes = $inputAttributes;
        $this->selectOptions = $selectOptions;
        $this->relationshipType = $relationshipType;
        $this->browsable = $browsable;
        $this->readable = $readable;
        $this->creatable = $creatable;
        $this->editable = $editable;
        $this->removable = $removable;
        $this->columnOrder = $columnOrder;
        $this->validationRulesForAdding = $validationRulesForAdding;
        $this->validationRulesForEditing = $validationRulesForEditing;
        $this->datatableClass = $datatableClass;
    }
}
