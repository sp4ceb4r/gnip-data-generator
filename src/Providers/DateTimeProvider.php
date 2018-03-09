<?php
/**
 * @author Jacob R. Schofield
 * @copyright HelpSocial
 */

namespace Sp4ceb4r\GnipDataGenerator\Providers;

use Carbon\Carbon;
use Faker\Generator;
use Faker\Provider\Base;

/**
 * Class DateTimeProvider.
 */
class DateTimeProvider extends Base
{
    protected $secondsInYear;

    public function __construct(Generator $generator)
    {
        parent::__construct($generator);

        $this->secondsInYear = 60 * 60 * 24 * 365;
    }

    public static function format(Carbon $dt, $format = 'atom+millis')
    {
        if ($format !== 'atom+millis') {
            return $dt->format($format);
        }

        return sprintf(
            "%s%03d%s",
            $dt->format("Y-m-d\TH:i:s."),
            floor($dt->format("u")/1000),
            $dt->format("O")
        );
    }

    public function datetimeString($timezone = null, $format = 'atom+millis')
    {
        return $this->format($this->datetime($timezone), $format);
    }

    public function datetime($timezone = null, $format = null)
    {
        $timezone = $timezone ?: null;

        $dt = Carbon::now($timezone)->addSeconds(mt_rand(-1 * $this->secondsInYear, $this->secondsInYear));
        if (!$format) {
            return $dt;
        }

        return $this->format($dt, $format);
    }

    public function datetimeFuture($timezone = null, $format = null)
    {
        $timezone = $timezone ?: null;

        $dt = Carbon::now($timezone)->addSeconds(mt_rand(1, $this->secondsInYear));
        if (!$format) {
            return $dt;
        }

        return $this->format($dt, $format);
    }

    public function datetimePast($timezone = null, $format = null)
    {
        $timezone = $timezone ?: null;

        $dt = Carbon::now($timezone)->subSeconds(mt_rand(1, $this->secondsInYear));
        if (!$format) {
            return $dt;
        }

        return $this->format($dt, $format);
    }

    /**
     * TODO: Description
     *
     * @return string
     */
    public function offsetString()
    {
        return (string) $this->offset();
    }

    /**
     * TODO: Description
     *
     * @return int
     */
    public function offset()
    {
        return Carbon::now($this->timezone())->getOffset();
    }

    /**
     * TODO: Description
     *
     * @return \DateTimeZone
     */
    public function timezone()
    {
        $options = \DateTimeZone::listIdentifiers();

        $timezone = $options[mt_rand(0, count($options) - 1)];

        return new \DateTimeZone($timezone);
    }
}
