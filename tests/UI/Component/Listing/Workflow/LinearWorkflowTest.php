<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component\Listing\Workflow;


class LinearWorkflowTest extends ILIAS_UI_TestBase {

	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Listing\Workflow\Factory();
	}

	public function setUp() {
		$f = $this->buildFactory();
		$this->title = 'title';
		$this->steps = [
			$f->step('', ''),
			$f->step('', '')
		];
		$this->wf = $f->linear($this->title, $this->steps);
	}

	public function test_implements_factory_interface() {
		$this->assertInstanceOf(Workflow\Workflow::class, $this->wf);

	}

	public function test_constructor_params() {
		$this->assertEquals($this->title, $this->wf->getTitle());
		$this->assertEquals($this->steps, $this->wf->getSteps());
		$this->assertEquals(0, $this->wf->getActive());
	}

	public function test_constructor() {
		$this->assertEquals($this->title, $this->wf->getTitle());
		$this->assertEquals($this->steps, $this->wf->getSteps());
		$this->assertEquals(0, $this->wf->getActive());
		$this->assertEquals(Workflow\Linear::HORIZONTAL, $this->wf->getOrientation());
	}

	public function test_amount_of_steps() {
		$this->assertEquals(count($this->steps), $this->wf->getAmountOfSteps());
	}

	public function test_active() {
		$wf = $this->wf->withActive(1);
		$this->assertEquals(1, $wf->getActive());
	}

	public function test_withActive_throws() {
		$raised = false;
		try {
			$this->wf->withActive(-1);
			$this->assertFalse("This should not happen.");
		} catch (\InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$this->wf->withActive(2);
			$this->assertFalse("This should not happen.");
		} catch (\InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}

	public function test_orientation() {
		$wf = $this->wf->withOrientation(Workflow\Linear::VERTICAL);
		$this->assertEquals(Workflow\Linear::VERTICAL, $wf->getOrientation());
	}

	public function test_withOrientation_throws() {
		$raised = false;
		try {
			$this->wf->withOrientation('wrong_orientation');
			$this->assertFalse("This should not happen.");
		} catch (\InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}

/*

	public function test_render() {
		$f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$name = "name_0";
		$pwd = $f->password($label, $byline)->withNameFrom($this->name_source);

		$r = $this->getDefaultRenderer();
		$expected = ""
			."<div class=\"form-group row\">"
				."<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
				."<div class=\"col-sm-9\">"
					."<input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
					."<div class=\"help-block\">$byline</div>"
				."</div>"
			."</div>";
		$this->assertHTMLEquals($expected, $r->render($pwd));
	}
	*/

}
