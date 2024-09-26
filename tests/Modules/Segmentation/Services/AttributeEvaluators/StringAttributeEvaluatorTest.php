<?php

namespace Featurit\Client\Tests\Modules\Segmentation\Services\AttributeEvaluators;

use Featurit\Client\Modules\Segmentation\ConstantCollections\StringOperators;
use Featurit\Client\Modules\Segmentation\Services\AttributeEvaluators\StringAttributeEvaluator;
use PHPUnit\Framework\TestCase;

class StringAttributeEvaluatorTest extends TestCase
{
    /**
     * Equals.
     *
     * @return void
     */
    public function test_equals_operator(): void
    {
        $stringAttributeEvaluator = new StringAttributeEvaluator();

        $this->assertTrue($stringAttributeEvaluator->evaluate(
            'TEST',
            StringOperators::EQUALS,
            'TEST'
        ));

        $this->assertFalse($stringAttributeEvaluator->evaluate(
            'TEST',
            StringOperators::EQUALS,
            'NOT_TEST'
        ));
    }

    /**
     * Not Equals.
     *
     * @return void
     */
    public function test_not_equals_operator(): void
    {
        $stringAttributeEvaluator = new StringAttributeEvaluator();

        $this->assertTrue($stringAttributeEvaluator->evaluate(
            'TEST',
            StringOperators::NOT_EQUALS,
            'NOT_TEST'
        ));

        $this->assertFalse($stringAttributeEvaluator->evaluate(
            'TEST',
            StringOperators::NOT_EQUALS,
            'TEST'
        ));
    }

    /**
     * Contains.
     *
     * @return void
     */
    public function test_contains_operator(): void
    {
        $stringAttributeEvaluator = new StringAttributeEvaluator();

        $this->assertTrue($stringAttributeEvaluator->evaluate(
            'THIS_TEST_CONTAINS',
            StringOperators::CONTAINS,
            'TEST'
        ));

        $this->assertFalse($stringAttributeEvaluator->evaluate(
            'THIS_TEST_CONTAINS',
            StringOperators::CONTAINS,
            'ELEPHANT'
        ));
    }

    /**
     * Is contained in.
     *
     * @return void
     */
    public function test_is_contained_in_operator(): void
    {
        $stringAttributeEvaluator = new StringAttributeEvaluator();

        $this->assertTrue($stringAttributeEvaluator->evaluate(
            'CHAIR',
            StringOperators::IS_CONTAINED_IN,
            'APPLE,LEMON,CHAIR'
        ));

        $this->assertFalse($stringAttributeEvaluator->evaluate(
            'email@no-reply.com',
            StringOperators::IS_CONTAINED_IN,
            'email@gmail.com,email@twitter.com,hello@outlook.com'
        ));
    }

    /**
     * Starts with.
     *
     * @return void
     */
    public function test_starts_with_operator(): void
    {
        $stringAttributeEvaluator = new StringAttributeEvaluator();

        $this->assertTrue($stringAttributeEvaluator->evaluate(
            'TEST_STARTS_WITH',
            StringOperators::STARTS_WITH,
            'TEST'
        ));

        $this->assertFalse($stringAttributeEvaluator->evaluate(
            'TEST_STARTS_WITH',
            StringOperators::STARTS_WITH,
            'ELEPHANT'
        ));
    }

    /**
     * Ends with.
     *
     * @return void
     */
    public function test_ends_with_operator(): void
    {
        $stringAttributeEvaluator = new StringAttributeEvaluator();

        $this->assertTrue($stringAttributeEvaluator->evaluate(
            'ENDS_WITH_TEST',
            StringOperators::ENDS_WITH,
            'TEST'
        ));

        $this->assertFalse($stringAttributeEvaluator->evaluate(
            'ENDS_WITH_TEST',
            StringOperators::ENDS_WITH,
            'ELEPHANT'
        ));
    }
}
