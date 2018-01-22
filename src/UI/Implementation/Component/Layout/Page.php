<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Page
 */
class Page implements C\Layout\Page {
	use ComponentHelper;

	/**
	 * @var 	mixed
	 */
	private $content;

	/**
	 * @var 	ILIAS\UI\Component\Layout\Metabar
	 */
	private $metabar;

	/**
	 * @var 	ILIAS\UI\Component\Layout\Sidebar
	 */
	private $sidebar;


	public function __construct($content) {
		$this->content = $content;
	}

	/**
	 * @inheritdoc
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @inheritdoc
	 */
	public function withContent($content) {
		$clone = clone $this;
		$clone->content = $content;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withMetabar(C\Layout\Metabar $metabar) {
		$clone = clone $this;
		$clone->metabar = $metabar;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getMetabar() {
		return $this->metabar;
	}

	/**
	 * @inheritdoc
	 */
	public function withSidebar(C\Layout\Sidebar $sidebar) {
		$clone = clone $this;
		$clone->sidebar = $sidebar;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getSidebar() {
		return $this->sidebar;
	}
}
