<?php
/**
 * @see       https://github.com/zendframework/zend-config for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-config/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Config\Processor;

class Constant extends Token implements ProcessorInterface
{
    /**
     * Replace only user-defined tokens
     *
     * @var bool
     */
    protected $userOnly = true;

    /**
     * Constant Processor walks through a Config structure and replaces all
     * PHP constants with their respective values
     *
     * @param bool   $userOnly              True to process only user-defined constants,
     *                                      false to process all PHP constants
     * @param string $prefix                Optional prefix
     * @param string $suffix                Optional suffix
     * @return \Zend\Config\Processor\Constant
     */
    public function __construct($userOnly = true, $prefix = '', $suffix = '')
    {
        $this->setUserOnly($userOnly);
        $this->setPrefix($prefix);
        $this->setSuffix($suffix);

        $this->loadConstants();
    }

    /**
     * @return bool
     */
    public function getUserOnly()
    {
        return $this->userOnly;
    }

    /**
     * Should we use only user-defined constants?
     *
     * @param  bool $userOnly
     * @return self
     */
    public function setUserOnly($userOnly)
    {
        $this->userOnly = (bool) $userOnly;
        return $this;
    }

    /**
     * Load all currently defined constants into parser.
     *
     * @return void
     */
    public function loadConstants()
    {
        if ($this->userOnly) {
            $constants = get_defined_constants(true);
            $constants = isset($constants['user']) ? $constants['user'] : [];
            $this->setTokens($constants);
        } else {
            $this->setTokens(get_defined_constants());
        }
    }

    /**
     * Get current token registry.
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Override processing of individual value.
     *
     * If the value is a string and evaluates to a class constant, returns
     * the class constant value; otherwise, delegates to the parent.
     *
     * @param mixed $value
     * @param array $replacements
     * @return mixed
     */
    protected function doProcess($value, array $replacements)
    {
        if (! is_string($value)) {
            return parent::doProcess($value, $replacements);
        }

        if (false === strpos($value, '::')) {
            return parent::doProcess($value, $replacements);
        }

        // Handle class constants
        if (defined($value)) {
            return constant($value);
        }

        // Handle ::class notation
        if (! preg_match('/::class$/i', $value)) {
            return parent::doProcess($value, $replacements);
        }

        $class = substr($value, 0, strlen($value) - 7);
        if (class_exists($class)) {
            return $class;
        }

        return parent::doProcess($value, $replacements);
    }
}
