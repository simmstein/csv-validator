csv-validator
=============

[![](https://phpci.gitnet.fr/build-status/image/2)](https://phpci.gitnet.fr/build-status/view/2)

CSV validator library

That uses the constraints of Symfony framework: [http://symfony.com/doc/current/reference/constraints.html](http://symfony.com/doc/current/reference/constraints.html).

* [Installation](#installation)
* [Example](#example)
* [Contributors](#contributors)

Installation
------------

You need [composer](https://getcomposer.org/):

	composer require deblan/csv-validator


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

// Initialisation of the validator
$validator = new Validator();

// The first field must contain an email
$validator->addFieldConstraint(0, new Email());

// The second field must contain a date
$validator->addFieldConstraint(1, new Date());

// Validate the legend
$validator->setExpectedHeaders(['foo', 'bar', 'bim']);

// An line must contain 3 columns
$validator->addDataConstraint(new Callback(function($data, ExecutionContextInterface $context) {
    if (count($data) !== 6) { // 6 because of the legend (3 fields * 2)
        $context->addViolation('The line must contain 3 columns');
    }
}));

// Initialisation of the parser
$parser = new CsvParser();
$parser->setHasHeaders(true);

$validator->validate($parser->parseFile(__DIR__.'/tests/fixtures/example.csv'));

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
```

Run `example.php` and see results:

```
<ul>
    <li>Line: 1</li>
    <li>Column: </li>
    <li>
        <p>Invalid legend.</p>
    </li>
</ul>
<ul>
    <li>Line: 4</li>
    <li>Column: </li>
    <li>
        <p>The line must contain 3 columns</p>
    </li>
</ul>
<ul>
    <li>Line: 2</li>
    <li>Column: 1</li>
    <li>
        <p>This value is not a valid email address.</p>
    </li>
</ul>
<ul>
    <li>Line: 3</li>
    <li>Column: 2</li>
    <li>
        <p>This value is not a valid date.</p>
    </li>
</ul>
```

Contributors
------------

* Simon Vieille
* Gautier Deruette
