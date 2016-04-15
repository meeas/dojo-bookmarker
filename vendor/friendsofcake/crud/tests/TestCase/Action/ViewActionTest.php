<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ViewActionTest extends IntegrationTestCase
{

    /**
     * fixtures property
     *
     * @var array
     */
    public $fixtures = ['plugin.crud.blogs'];

    /**
     * Data provider with all HTTP verbs
     *
     * @return array
     */
    public function allHttpMethodProvider()
    {
        return [
            ['get'],
            ['post'],
            ['put'],
            ['delete']
        ];
    }

    /**
     * Test the normal HTTP flow for all HTTP verbs
     *
     * @dataProvider allHttpMethodProvider
     * @return void
     */
    public function testGet($method)
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_subscribeToEvents($this->_controller);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->{$method}('/blogs/view/1');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('blog'));
        $this->assertNotNull($this->viewVariable('success'));
    }

    /**
     * Test that changing the viewVar reflects in controller::$viewVar
     *
     * @return void
     */
    public function testGetWithViewVar()
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Crud->action('view')->viewVar('item');
                $this->_subscribeToEvents($this->_controller);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->get('/blogs/view/1');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('item'));
        $this->assertNotNull($this->viewVariable('success'));
    }
}