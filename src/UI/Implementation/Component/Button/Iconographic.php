<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;

/**
 * iconographic button
 */
class Iconographic extends Button implements C\Button\Iconographic {

	/**
	 * @var ILIAS\UI\Component\Icon
	 */
	protected $icon;

	/**
	 * @var bool
	 */
	protected $engaged = false;


	public function __construct($icon, $label, $action) {
		$this->checkStringArg("label", $label);
		$this->checkStringArg("action", $action);
		$this->icon = $icon;
		$this->label = $label;
		$this->action = $action;
	}

	/**
	 * @inheritdoc
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @inheritdoc
	 */
	public function withEngagedState($state) {
		$this->checkBoolArg("state", $state);
		$clone = clone $this;
		$clone->engaged =$state;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function isEngaged() {
		return $this->engaged;
	}


}
