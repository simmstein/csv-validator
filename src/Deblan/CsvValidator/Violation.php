<?php

namespace Deblan\CsvValidator;

use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class Violation.
 *
 * @author Simon Vieille <simon@deblan.fr>
 */
class Violation
{
    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    /**
     * @var ConstraintViolation
     */
    protected $violation;

    /**
     * Constructor.
     *
     * @param int                 $line      The line of the violation
     * @param int                 $column    The column of the violation
     * @param ConstraintViolation $violation The violation
     */
    public function __construct($line, $column, ConstraintViolation $violation)
    {
        $this->setLine($line)
            ->setColumn($column)
            ->setViolation($violation);
    }

    /**
     * @param int $line
     *
     * @return Violation
     */
    public function setLine($line)
    {
        $this->line = (int) $line;

        return $this;
    }

    /**
     * @return int $line
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param int $column
     *
     * @return Violation
     */
    public function setColumn($column)
    {
        if ($column !== null) {
            $column = (int) $column;
        }

        $this->column = $column;

        return $this;
    }

    /**
     * @return int $column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param ConstraintViolation $violation
     *
     * @return Violation
     */
    public function setViolation(ConstraintViolation $violation)
    {
        $this->violation = $violation;

        return $this;
    }

    /**
     * @return ConstraintViolation
     */
    public function getViolation()
    {
        return $this->violation;
    }
}
