<?php

namespace Deblan\CsvValidator;

use Deblan\Csv\CsvParser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\LegacyValidator;
use Symfony\Component\Validator\ConstraintViolationList;

class Validator
{
    protected $parser;

    protected $validator;

    protected $fieldsConstraints = [];

    protected $dataConstraints = [];

    protected $hasValidate = false;

    protected $errors = [];

    public function __construct(CsvParser $parser, LegacyValidator $validator)
    {
        $this->parser = $parser;
        $this->parser->parse();
        $this->validator = $validator;
    }

    public function addFieldConstraint($key, Constraint $constraint)
    {
        if (!array_key_exists($key, $this->fieldsConstraints)) {
            $this->fieldsConstraints[$key] = [];
        }

        $this->fieldsConstraints[$key][] = $constraint;

        return $this;
    }

    public function addDataContraint(Constraint $constraint)
    {
        $this->dataConstraints[] = $constraint;

        return $this;
    }

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

    protected function mergeViolationsMessages(ConstraintViolationList $violations, $line, $key = null)
    {
        if (!array_key_exists($line, $this->errors)) {
            $this->errors[$line] = [];
        }

        if (is_int($key)) {
            $key++;
        }

        foreach ($violations as $violation) {
            $message = sprintf('Line %d%s: %s', $line + 1, $key !== null ? ', field '.($key) : '', $violation->getMessage());

            $this->errors[$line][] = $message;
        }
    }

    protected function mergeErrorMessage($message, $line, $key = null)
    {
        if (!array_key_exists($line, $this->errors)) {
            $this->errors[$line] = [];
        }

        if (is_int($key)) {
            $key++;
        }

        $message = sprintf('Line %d%s: %s', $line + 1, $key !== null ? ', field '.($key) : '', $message);

        $this->errors[$line][] = $message;
    }

    public function isValid()
    {
        if (!$this->hasValidate) {
            throw new \RuntimeException('You must validate before.');
        }

        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
