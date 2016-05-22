csv-validator
=============

CSV validator library

That uses the constraints of Symfony framework: [http://symfony.com/doc/current/reference/constraints.html](http://symfony.com/doc/current/reference/constraints.html).

Installation
------------

You need [composer](https://getcomposer.org/):

	composer require deblan/csv-validator dev-master


Example
-------

```
<?php

use Deblan\Csv\CsvParser;
use Deblan\CsvValidator\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

require __DIR__.'/vendor/autoload.php';

// Initialisation of the parser
$parser = new CsvParser(__DIR__.'/tests/fixtures/example.csv');
$parser->setHasLegend(true);

// Initialisation of the validator
$validator = new Validator($parser, Validation::createValidator());

// The first field must contain an email
$validator->addFieldConstraint(0, new Email());

// The second field must contain a date
$validator->addFieldConstraint(1, new Date());

// An line must contain 3 columns
$validator->addDataConstraint(new Callback(function($data, ExecutionContextInterface $context) {
    if (count($data) !== 6) { // 6 because of the legend (3 fields * 2)
        $context->addViolation('The line must contain 3 columns');
    }
}));

$validator->validate();

if ($validator->isValid() === false) {
    foreach ($validator->getErrors() as $violation) {
        $line = $violation->getLine(); 
        $column = $violation->getColumn();
        $message = $violation->getViolation()->getMessage();
        
        // Up to you!
    }
}
```
