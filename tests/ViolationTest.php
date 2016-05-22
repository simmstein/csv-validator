<?php

use Symfony\Component\Validator\ConstraintViolation;
use Deblan\CsvValidator\Violation;

class ViolationTest extends \PHPUnit_Framework_TestCase
{
    public function testViolation()
    {
        $constraintViolation = $this->generateConstraintViolation();

        $violation = new Violation(1, 2, $constraintViolation);

        $this->assertEquals(1, $violation->getLine());
        $this->assertEquals(2, $violation->getColumn());
        $this->assertEquals($constraintViolation, $violation->getViolation());
    }

    protected function generateConstraintViolation()
    {
        return new ConstraintViolation('foo', 'foo', [], null, '', null);
    }
}
