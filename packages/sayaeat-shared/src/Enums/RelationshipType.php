<?php

namespace SayaEat\Shared\Enums;

enum RelationshipType : string
{
    case BELONGS_TO = 'belongsTo';
    case BELONGS_TO_MANY = 'belongsToMany';
    case HAS_ONE = 'hasOne';
    case HAS_MANY = 'hasMany';
}
