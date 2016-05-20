<?php

use Deblan\Csv\CsvParser;
use Deblan\CsvValidator\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Date;

chdir(__DIR__);

require 'vendor/autoload.php';

$parser = new CsvParser('example.csv', ';', '');

$validator = new Validator($parser, Validation::createValidator());

$validator->addFieldConstraint(0, new Email());
$validator->addFieldConstraint(1, new Date());

$validator->validate();

if ($validator->isValid() === false) {
    foreach ($validator->getErrors() as $violation) {
        $line = $violation->getLine(); 
        $column = $violation->getColumn();
        $message = $violation->getViolation()->getMessage();

        echo <<<EOF
Line   : $line
Column : $column
Message: $message


EOF;

    }
}
