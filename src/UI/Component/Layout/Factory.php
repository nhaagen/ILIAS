<?php
namespace ILIAS\UI\Component\Layout;
/**
 * This is what a factory for layout-elements looks like.
 */
interface Factory {

    /**
     * ---
     * description:
     *   purpose: >
     *
     *
     *   composition: >
     *
     *
     *   effect: >
     *
     *
     *   rivals:
     *     SideBar: >
     *
     * rules:
     *   usage:
     *     1: The sidebar is unique for the page - there MUST be but one.
     *
     *   composition:
     *     1:
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\MetaBar
     */
    public function metabar();

    /**
     * ---
     * description:
     *   purpose: >
     *     The sidebar is a unique page section that bundles access to
     *     content-based navigational strategies (like search or the repository tree)
     *     as well as navigation to services unrelated to the actual content,
     *     like the user's profile or administrative settings.
     *
     *     The contents of the bar are never modified by changing context,
     *     but may vary according to e.g. the current user's permissions.
     *
     *   composition: >
     *     The sidebar holds Iconographic Buttons. Usually, a button is associated
     *     with a Slate that provides further navigational options.
     *
     *   effect: >
     *     The Sidebar is always visible and available (except in exam/kiosk mode).
     *
     *     In a desktop environment, a vertical bar is rendered on the left side
     *     of the screen covering the full height minus the header-area.
     *     Entries are aligned vertically.
     *
     *     Like the header, the bar is a static screen element unaffected by scrolling.
     *     Thus, entries will become inaccessible when the window is of smaller height
     *     than the height of all entries together.
     *
     *     The contents of the bar itself will not scroll.
     *
     *     Width of content- and footer-area is limited to a maximum of the
     *     overall available width minus that of the bar.
     *
     *     For mobile devices, the bar is rendered horizontally on the bottom
     *     of the screen with the entries aligned horizontally.
     *     Again, entries will become inacessible, if the window/screen is smaller
     *     than the width of all entries summed up.
     *
     *     When clicking a button, usually a Slate with further options is expanded.
     *     There is but one active slate in the bar.
     *     Iconographic buttons in the sidebar are stateful, i.e. they have a
     *     pressed-status that can either be toggled by clicking the same button again
     *     or by clicking a different button.
     *
     *   rivals:
     *     Tab Bar: >
     *       The sidebar (and its components) shall not be used to substitute
     *       functionality available at objects, such as settings, members or
     *       learning progress. Those remain in the Tab Bar.
     *
     *     Content Actions: >
     *       Also, adding new items, the actions-menu (with comments, notes and tags),
     *       moving, linking or deleting objects and the like are not part of
     *       the sidebar.
     *
     *     Personal Desktop: >
     *       The Personal Desktop provides access to services and tools and
     *       displays further information at first glance (e.g. the calendar).
     *       The sidebar may reference those tools as well, but rather in form
     *       of a link than a widget.
     *
     *     Notification Center: >
     *       Notifications of the system to the user, e.g. new Mail, are placed
     *       in the Notification Center.
     *       The direction of communication for the sidebar is "user to system",
     *       while the direction is "system to user" in the Notification Center.
     *       However, navigation from both components can lead to the same page.
     *
     *     Modal: >
     *       Forms with the intention of modifying the content are placed in modals
     *       or on the content-page.
     *
     * rules:
     *   usage:
     *     1: The sidebar is unique for the page - there MUST be but one.
     *
     *   composition:
     *     1: The bar MUST NOT contain items other than buttons.
     *     2: The bar MUST contain at least one button.
     *     3: The bar SHOULD NOT contain more than five buttons.
     *
     *   style:
     *     1: The bar MUST have a fixed witdth (desktop).
     *     2: The bar MUST have a fixed height (mobile).
     *
     *   interaction:
     *     1: >
     *        Operating elements in the bar MUST either lead to further
     *        navigational options within the bar (open a slate, open a plank)
     *        OR actually invoke navigation, i.e. change the location/content
     *        of the current page.
     *     2: Elements in the bar MUST NOT open a modal or window.
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\SideBar
     */
    public function sidebar();


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A layout-component (or page-element) describes a section of the ILIAS UI;
      *     the page thus is the user's view upon ILIAS in total.
	 *
	 *
	 *
	 * ----
	 *
	 * @return  \ILIAS\UI\Component\Layout\Page
	 */
	public function page($content);

}
