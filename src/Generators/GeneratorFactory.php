<?php
/**
 * @author Jacob R. Schofield
 * @copyright HelpSocial
 */

namespace Sp4ceb4r\GnipDataGenerator\Generators;

/**
 * Class GeneratorFactory.
 */
class GeneratorFactory
{
    protected $generators = [];

    public function make($name)
    {
        if (!isset($this->generators[$name])) {
            $this->generators[$name] = $this->build($name);
        }

        return $this->generators[$name];
    }

    protected function build($name)
    {
        switch ($name) {
            case 'powertrack':
                return new PowerTrackGenerator();
            default:
                throw new \LogicException("Unknown generator: $name");
        }
    }
}
