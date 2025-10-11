<?php

namespace SayaEat\Shared\Enums;

enum InputType: string
{
    case DATE = 'date';
    case DATETIME = 'datetime';
    case FILE = 'file';
    case HIDDEN = 'hidden';
    case TEXT = 'text';
    case SELECT = 'select';
    case SELECT_MULTIPLE = 'select_multiple';
    case IMAGE = 'image';
    case NUMERIC = 'numeric';
    case NUMBER = 'number';
    case DECIMAL = 'decimal';
    case PASSWORD = 'password';
    case RELATIONSHIP = 'relationship';
}
