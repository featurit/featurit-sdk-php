<?php

namespace Featurit\Client\Tests\Modules\Segmentation\Services\AttributeEvaluators;

use Featurit\Client\Modules\Segmentation\ConstantCollections\NumberOperators;
use Featurit\Client\Modules\Segmentation\Services\AttributeEvaluators\NumberAttributeEvaluator;
use PHPUnit\Framework\TestCase;

class NumberAttributeEvaluatorTest extends TestCase
{
    /**
     * Less than.
     *
     * @return void
     */
    public function test_less_than_operator(): void
    {
        $numberAttributeEvaluator = new NumberAttributeEvaluator();

        $this->assertTrue($numberAttributeEvaluator->evaluate(
            1,
            NumberOperators::LESS_THAN,
            2
        ));

        $this->assertFalse($numberAttributeEvaluator->evaluate(
            2,
            NumberOperators::LESS_THAN,
            1
        ));
    }

    /**
     * Less equal than.
     *
     * @return void
     */
    public function test_less_equal_than_operator(): void
    {
        $numberAttributeEvaluator = new NumberAttributeEvaluator();

        $this->assertTrue($numberAttributeEvaluator->evaluate(
            1,
            NumberOperators::LESS_EQUAL_THAN,
            2
        ));

        $this->assertTrue($numberAttributeEvaluator->evaluate(
            2,
            NumberOperators::LESS_EQUAL_THAN,
            2
        ));

        $this->assertFalse($numberAttributeEvaluator->evaluate(
            2,
            NumberOperators::LESS_EQUAL_THAN,
            1
        ));
    }

    /**
     * Equal.
     *
     * @return void
     */
    public function test_equal_operator(): void
    {
        $numberAttributeEvaluator = new NumberAttributeEvaluator();

        $this->assertTrue($numberAttributeEvaluator->evaluate(
            1,
            NumberOperators::EQUAL,
            1
        ));

        $this->assertFalse($numberAttributeEvaluator->evaluate(
            1,
            NumberOperators::EQUAL,
            2
        ));
    }

    /**
     * Not equal.
     *
     * @return void
     */
    public function test_not_equal_operator(): void
    {
        $numberAttributeEvaluator = new NumberAttributeEvaluator();

        $this->assertTrue($numberAttributeEvaluator->evaluate(
            1,
            NumberOperators::NOT_EQUAL,
            2
        ));

        $this->assertFalse($numberAttributeEvaluator->evaluate(
            2,
            NumberOperators::NOT_EQUAL,
            2
        ));
    }

    /**
     * Greater equal than.
     *
     * @return void
     */
    public function test_greater_equal_than_operator(): void
    {
        $numberAttributeEvaluator = new NumberAttributeEvaluator();

        $this->assertTrue($numberAttributeEvaluator->evaluate(
            2,
            NumberOperators::GREATER_EQUAL_THAN,
            1
        ));

        $this->assertTrue($numberAttributeEvaluator->evaluate(
            1,
            NumberOperators::GREATER_EQUAL_THAN,
            1
        ));

        $this->assertFalse($numberAttributeEvaluator->evaluate(
            1,
            NumberOperators::GREATER_EQUAL_THAN,
            2
        ));
    }
}
