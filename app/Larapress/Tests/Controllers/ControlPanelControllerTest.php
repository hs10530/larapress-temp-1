<?php namespace Larapress\Tests\Controllers;

use Larapress\Controllers\ControlPanelController;
use Larapress\Tests\Controllers\Templates\ControllerTestCase;
use Mockery;
use Mockery\Mock;

class ControlPanelControllerTest extends ControllerTestCase {

	/**
	 * @var Mock
	 */
	private $view;

	public function setUp()
	{
		parent::setUp();

		$this->view = Mockery::mock('\Illuminate\View\Factory');
	}

	protected function getControlPanelControllerInstance()
	{
		return new ControlPanelController($this->helpers, $this->view);
	}

	/**
	 * @test getDashboard() sets the page title and makes the dashboard view
	 */
	public function getDashboard_sets_the_page_title_and_makes_the_dashboard_view()
	{
		$this->helpers->shouldReceive('setPageTitle')->with('Dashboard')->once();
		$this->view->shouldReceive('make')->with('larapress::pages.cp.dashboard')->once()->andReturn('foo');
		$controller = $this->getControlPanelControllerInstance();

		$this->assertEquals('foo', $controller->getDashboard());
	}

}
