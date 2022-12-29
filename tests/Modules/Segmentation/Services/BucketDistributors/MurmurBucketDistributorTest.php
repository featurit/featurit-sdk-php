<?php

namespace Featurit\Client\Tests\Modules\Segmentation\Services\BucketDistributors;

use Featurit\Client\Modules\Segmentation\Services\BucketDistributors\MurmurBucketDistributor;
use PHPUnit\Framework\TestCase;

class MurmurBucketDistributorTest extends TestCase
{
    private \Faker\Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = \Faker\Factory::create();
    }

    /**
     * It returns a number between 1 and 100
     *
     * @return void
     */
    public function test_it_returns_numbers_between_1_and_100(): void
    {
        foreach (range(0, 100) as $number) {

            $this->assert_between_1_and_100(
                $this->faker->sentence(2),
                $this->faker->uuid()
            );
        }
    }

    /**
     * Rollout attribute values can be integers
     *
     * @return void
     */
    public function test_rollout_attribute_values_can_be_integers(): void
    {
        foreach (range(0, 100) as $number) {

            $this->assert_between_1_and_100(
                $this->faker->sentence(2),
                $this->faker->numberBetween()
            );
        }
    }

    /**
     * Rollout attribute values can be null
     *
     * @return void
     */
    public function test_rollout_attribute_values_can_be_null(): void
    {
        foreach (range(0, 100) as $number) {

            $this->assert_between_1_and_100(
                $this->faker->sentence(2),
                null
            );
        }
    }

    private function assert_between_1_and_100(string $featureName, string|null $featureRolloutAttributeValue): void
    {
        $rolloutBucketCalculator = new MurmurBucketDistributor();

        $rolloutBucket = $rolloutBucketCalculator->distribute(
            $featureName,
            $featureRolloutAttributeValue
        );

        $this->assertIsInt($rolloutBucket);

        $this->assertLessThanOrEqual(100, $rolloutBucket);
        $this->assertGreaterThanOrEqual(1, $rolloutBucket);

    }
}
