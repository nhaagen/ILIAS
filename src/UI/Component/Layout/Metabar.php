<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Layout;
use \ILIAS\UI\Component as C;

/**
 * This describes the Metabar.
 */
interface Metabar extends C\Component {

	/**
	 * @param 	\ILIAS\UI\Component\Image\Image
	 * @return 	Metabar
	 */
	public function withLogo($logo);

	/**
	 *  @return 	\ILIAS\UI\Component\Image\Image
	 */
	public function getLogo();

	/**
	 * @param 	mixed 	$element
	 * @return 	Metabar
	 */
	public function withElement($element);

	/**
	 * @return 	mixed[]
	 */
	public function getElements();

}