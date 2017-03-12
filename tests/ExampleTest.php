<?php

/**
 * class ExampleTest.
 *
 * @author Simon Vieille <simon@deblan.fr>
 */
class ExampleTest extends \PHPUnit_Framework_TestCase
{
    public function testExemple()
    {
        $content = shell_exec('php -f '.__DIR__.'/../example.php');

        $this->assertEquals('<ul>
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
', $content);
    }
}
