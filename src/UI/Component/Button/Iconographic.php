<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * This describes an iconographic button.
 */
interface Iconographic extends Button {

	/**
	 * @return ILIAS\UI\Component\Icon
	 */
	public function getIcon();

	/**
	 * @param 	bool 	$state
	 * @return Iconographic
	 */
	public function withEngagedState($state);

	/**
	 * @return bool
	 */
	public function isEngaged();



}