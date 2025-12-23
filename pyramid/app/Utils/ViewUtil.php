<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Support\Collection;

class ViewUtil
{
    public static function toSelectOptions(Collection | DBCollection $collection, String $fieldForIdentifier = 'id', String $fieldForLabel = 'name'): array
    {
        return $collection->reduce(function($prev, $item) use ($fieldForIdentifier, $fieldForLabel) {
            $prev[$item[$fieldForLabel]] = $item[$fieldForIdentifier];
            return $prev;
        }, []);
    }
}
