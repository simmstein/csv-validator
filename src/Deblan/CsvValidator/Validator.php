<?php

namespace Deblan\CsvValidator;

use Deblan\Csv\CsvParser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class Validator
 * @author Simon Vieille <simon@deblan.fr>
 */
class Validator
{
    /**
     * @var CsvParser
     */
    protected $parser;

    /**
     * @var RecursiveValidator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $fieldsConstraints = [];

    /**
     * @var array
     */
    protected $dataConstraints = [];

    /**
     * @var boolean
     */
    protected $hasValidate = false;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor
     *
     * @param CsvParser $parser
     * @param RecursiveValidator $validator
     */
    public function __construct(CsvParser $parser, RecursiveValidator $validator)
    {
        $this->parser = $parser;
        $this->parser->parse();
        $this->validator = $validator;
    }

    /**
     * Append a constraint to a specific column
     *
     * @param $key The column number
     * @param Constraint $constraint The constraint
     * @return Validator
     */
    public function addFieldConstraint($key, Constraint $constraint)
    {
        if (!array_key_exists($key, $this->fieldsConstraints)) {
            $this->fieldsConstraints[$key] = [];
        }

        $this->fieldsConstraints[$key][] = $constraint;

        return $this;
    }

    /**
     * Append a constraint to a specific line
     *
     * @param $key The column number
     * @param Constraint $constraint The constraint
     * @return Validator
     */
    public function addDataConstraint(Constraint $constraint)
    {
        $this->dataConstraints[] = $constraint;

        return $this;
    }

    /**
     * Run the validation
     */
    public function validate()
    {
        if ($this->hasValidate) {
            return;
        }

        foreach ($this->parser->getDatas() as $line => $data) {
            foreach ($this->dataConstraints as $constraint) {
                $violations = $this->validator->validateValue($data, $constraint);

                $this->mergeViolationsMessages($violations, $line);
            }

            foreach ($this->fieldsConstraints as $key => $constraints) {
                if (!isset($data[$key])) {
                    $this->mergeErrorMessage(sprintf('Field "%s" does not exist.', $key + 1), $line, $key);
                } else {
                    foreach ($constraints as $constraint) {
                        $violations = $this->validator->validateValue($data[$key], $constraint);

                        $this->mergeViolationsMessages($violations, $line, $key);
                    }
                }
            }
        }

        $this->hasValidate = true;
    }

    /**
     * Add violations
     *
     * @param ConstraintViolationList $violations
     * @param integer $line The line of the violations
     * @param integer|null $key The column of the violations
     */
    protected function mergeViolationsMessages(ConstraintViolationList $violations, $line, $key = null)
    {
        if (count($violations) === 0) {
            return;
        }

        if (is_int($key)) {
            $key++;
        }

        foreach ($violations as $violation) {
            $this->errors[] = new Violation($line + 1, $key, $violation);
        }
    }

    /**
     * Create and append a violation from a string error
     *
     * @param string $message The error message
     * @param integer $line The line of the violations
     * @param integer|null $key The column of the violations
     */
    protected function mergeErrorMessage($message, $line, $key = null)
    {
        if (!array_key_exists($line, $this->errors)) {
            $this->errors[$line] = [];
        }

        if (is_int($key)) {
            $key++;
        }

        $violation = new ConstraintViolation($message, $message, [], null, '', null);
        $this->errors[] = new Violation($line + 1, $key, $violation);
    }

    /**
     * Returns the validation status
     *
     * @return boolean
     * @throw RuntimeException No validation yet
     */
    public function isValid()
    {
        if (!$this->hasValidate) {
            throw new \RuntimeException('You must validate before.');
        }

        return empty($this->errors);
    }

    /**
     * Returns the errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}