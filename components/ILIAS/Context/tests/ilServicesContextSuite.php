<?php

use PHPUnit\Framework\TestSuite;

/**
 * Context Test-Suite
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilServicesContextSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilServicesContextSuite();

        require_once(__DIR__ . "/ilContextTest.php");

        $suite->addTestSuite("ilContextTest");

        return $suite;
    }
}
