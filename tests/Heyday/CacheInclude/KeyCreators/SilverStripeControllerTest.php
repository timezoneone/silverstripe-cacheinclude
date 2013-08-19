<?php

namespace Heyday\CacheInclude\KeyCreators;

class SilverStripeControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SilverStripeController
     */
    protected $keyCreator;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $controllerMock;

    protected function setUp()
    {
        \Versioned::choose_site_stage();
        \Config::inst()->update('SSViewer', 'theme', 'theme');
        $this->controllerMock = $this->getMock('Controller');
        $this->keyCreator = new SilverStripeController($this->controllerMock);
    }

    protected function tearDown()
    {
        $this->keyCreator = null;
    }

    public function testNoContext()
    {
        $this->assertEquals(
            'theme.Live.test',
            $this->keyCreator->getKey(
                'test',
                array(
                    'context' => 'no'
                )
            )
        );
    }

    public function testPageContext()
    {
        $this->controllerMock->expects($this->once())
            ->method('getURLParams')
            ->will(
                $this->returnValue(
                    array(
                        'URLSegment' => 'testing'
                    )
                )
            );

        $this->assertEquals(
            'theme.Live.testing.test',
            $this->keyCreator->getKey(
                'test',
                array(
                    'context' => 'page'
                )
            )
        );
    }

    public function testUrlParamsContext()
    {
        $this->controllerMock->expects($this->once())
            ->method('getURLParams')
            ->will(
                $this->returnValue(
                    array(
                        'URLSegment' => '1',
                        'Action'     => '2',
                        'ID'         => '3'
                    )
                )
            );
        
        $this->assertEquals(
            'theme.Live.1.2.3.test',
            $this->keyCreator->getKey(
                'test',
                array(
                    'context' => 'url-params'
                )
            )
        );
    }

    public function testFullPageContext()
    {
        $request = $this->getMock('SS_HTTPRequest', array(), array(), '', false);
        $request->expects($this->once())
            ->method('requestVars')
            ->will(
                $this->returnValue(
                    array(
                        'url'   => '1/2/3',
                        'var1'  => 'test',
                        'flush' => 1
                    )
                )
            );

        $this->controllerMock->expects($this->once())
            ->method('getRequest')
            ->will(
                $this->returnValue($request)
            );
        
        $this->assertEquals(
            'theme.Live.' . md5(http_build_query(array('url' => '1/2/3', 'var1' => 'test'))) . '.test',
            $this->keyCreator->getKey(
                'test',
                array(
                    'context' => 'full'
                )
            )
        );
    }

    public function testControllerContext()
    {
        $this->assertEquals(
            'theme.Live.'.get_class($this->controllerMock).'.test',
            $this->keyCreator->getKey(
                'test',
                array(
                    'context' => 'controller'
                )
            )
        );
    }
}
