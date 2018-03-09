<?php
/**
 * @author Jacob R. Schofield
 * @copyright HelpSocial
 */

namespace Sp4ceb4r\GnipDataGenerator\Providers;

use Faker\Generator;
use Faker\Provider\Base;

/**
 * Class EngagementProvider.
 */
class EngagementProvider extends Base
{
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    public function likes($min = 0, $max = 1000)
    {
        return mt_rand($min, $max);
    }

    public function shares($min = 0, $max = 1000)
    {
        return mt_rand($min, $max);
    }

    public function statuses($min = 1, $max = 100000)
    {
        return $this->generator->numberBetween($min, $max);
    }
}
