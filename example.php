<?php

use Deblan\Csv\CsvParser;
use Deblan\CsvValidator\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

require __DIR__.'/vendor/autoload.php';

// Initialisation of the validator
$validator = new Validator();

// The first field must contain an email
$validator->addFieldConstraint(0, new Email());

// The second field must contain a date
$validator->addFieldConstraint(1, new Date());

// Validate the legend
$validator->setExpectedLegend(array('foo', 'bar', 'bim'));

// An line must contain 3 columns
$validator->addDataConstraint(new Callback(function($data, ExecutionContextInterface $context) {
    if (count($data) !== 6) { // 6 because of the legend (3 fields * 2)
        $context->addViolation('The line must contain 3 columns');
    }
}));

// Initialisation of the parser
$parser = new CsvParser(__DIR__.'/tests/fixtures/example.csv');
$parser->setHasLegend(true);

$validator->validate($parser);

if ($validator->isValid() === false) {
    foreach ($validator->getErrors() as $error) {
        $line = $error->getLine(); 
        $column = $error->getColumn();
        $message = $error->getViolation()->getMessage();
        
        echo <<<EOF
<ul>
    <li>Line: $line</li>
    <li>Column: $column</li>
    <li>
        <p>$message</p>
    </li>
</ul>

EOF;
    }
}
