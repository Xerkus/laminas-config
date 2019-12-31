<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config;

use Laminas\Config\AbstractConfigFactory;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager;

/**
 * Class AbstractFactoryTest
 */
class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Laminas\Mvc\Application
     */
    protected $application;

    /**
     * @var \Laminas\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @return void
     */
    public function setUp()
    {
        $config = array(
            'MyModule' => array(
                'foo' => array(
                    'bar'
                )
            ),
            'phly-blog' => array(
                'foo' => array(
                    'bar'
                )
            )
        );

        $sm = $this->serviceManager = new ServiceManager\ServiceManager(
            new ServiceManagerConfig(array(
            'abstract_factories' => array(
                'Laminas\Config\AbstractConfigFactory',
            )
            ))
        );

        $sm->setService('Config', $config);
    }

    /**
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testInvalidPattern()
    {
        $factory = new AbstractConfigFactory();
        $factory->addPattern(new \stdClass());
    }

    /**
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testInvalidPatternIterator()
    {
        $factory = new AbstractConfigFactory();
        $factory->addPatterns('invalid');
    }

    /**
     * @return void
     */
    public function testPatterns()
    {
        $factory = new AbstractConfigFactory();
        $defaults = $factory->getPatterns();

        // Tests that the accessor returns an array
        $this->assertInternalType('array', $defaults);
        $this->assertGreaterThan(0, count($defaults));

        // Tests adding a single pattern
        $this->assertSame($factory, $factory->addPattern('#foobarone#i'));
        $this->assertCount(count($defaults) + 1, $factory->getPatterns());

        // Tests adding multiple patterns at once
        $patterns = $factory->getPatterns();
        $this->assertSame($factory, $factory->addPatterns(array('#foobartwo#i', '#foobarthree#i')));
        $this->assertCount(count($patterns) + 2, $factory->getPatterns());

        // Tests whether the latest added pattern is the first in stack
        $patterns = $factory->getPatterns();
        $this->assertSame('#foobarthree#i', $patterns[0]);
    }

    /**
     * @return void
     */
    public function testCanCreateService()
    {
        $factory = new AbstractConfigFactory();
        $serviceLocator = $this->serviceManager;

        $this->assertFalse($factory->canCreateServiceWithName($serviceLocator, 'mymodulefail', 'MyModule\Fail'));
        $this->assertTrue($factory->canCreateServiceWithName($serviceLocator, 'mymoduleconfig', 'MyModule\Config'));
    }

    /**
     * @depends testCanCreateService
     * @return void
     */
    public function testCreateService()
    {
        $serviceLocator = $this->serviceManager;
        $this->assertInternalType('array', $serviceLocator->get('MyModule\Config'));
        $this->assertInternalType('array', $serviceLocator->get('MyModule_Config'));
        $this->assertInternalType('array', $serviceLocator->get('Config.MyModule'));
        $this->assertInternalType('array', $serviceLocator->get('phly-blog.config'));
        $this->assertInternalType('array', $serviceLocator->get('phly-blog-config'));
        $this->assertInternalType('array', $serviceLocator->get('config-phly-blog'));
    }
}
