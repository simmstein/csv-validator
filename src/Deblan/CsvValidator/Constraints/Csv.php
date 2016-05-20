<?php

namespace Deblan\CsvValidator\Constraints;

use Symfony\Component\Validator\Constraints\File;

class Csv extends File
{
    const FIELD_NOT_VALID = 10;
    const FIELD_NOT_DETECTED = 11;
    const LINE_NOT_VALID = 12;

    protected static $errorNames = [
        self::FIELD_NOT_VALID = 'FIELD_NOT_VALID',
        self::FIELD_NOT_DETECTED = 'FIELD_NOT_DETECTED',
        self::LINE_NOT_VALID = 'LINE_NOT_VALID',
    ];

    public $fieldsConstraints = [];
    public $lineConstraints = [];

    public $fieldNotValidMessage = 'The field {{ field }} of the line {{ line }} is not valid. {{ message }}';
    public $fieldNotDetectedMessage = 'The field {{ field }} of the line {{ line }} is missing.';
    public $lineNotValidMessage = 'The line {{ line }} is not valid. {{ message }}';
}
