<?php

namespace App\Traits;

use App\Core\AppFieldDef;

trait HasFieldDef
{
    public function getValidationRulesForAdding(): array
    {
        $rules = [];

        foreach ($this->fieldDefs as $field) {
            if (empty($field->validationRulesForAdding)) {
                continue;
            }
            $rules[$field->column] = $field->validationRulesForAdding;
        }

        return $rules;
    }

    public function getValidationRulesForEditing(): array
    {
        $rules = [];

        foreach ($this->fieldDefs as $field) {
            if (empty($field->validationRulesForEditing)) {
                continue;
            }
            $rules[$field->column] = $field->validationRulesForEditing;
        }

        return $rules;
    }

    public function getCreatableColumn(): array
    {
        return collect($this->fieldDefs)->filter(function(AppFieldDef $item) {
           return $item->creatable;
        })
            ->pluck('column')
            ->toArray();
    }

    public function getEditableColumn(): array
    {
        return collect($this->fieldDefs)->filter(function(AppFieldDef $item) {
            return $item->editable;
        })
            ->pluck('column')
            ->toArray();
    }
}
