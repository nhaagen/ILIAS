<?php
namespace ILIAS\UI\Component\MainControls\Prompts;

/**
 * This is what a factory for prompts looks like.
 */

interface Factory {

	/**
	 * ---
     * description:
     *   purpose: >
     *     The Notification Center concentrates the visualization of system notifications
     *     into one expandable glyph.
     *     This unification removes the multitude of dedicated notification-icons
     *     in favor of visual cleanliness as well as providing a designated
     *     location for further extensions.
     *
     *   composition: >
     *      The Notification Center is visualized as a glyph with a counter.
     *      Clicked, a pop-over list with the notifying services and their
     *      respective counter-glyphs is expanded.
     *
     *   effect: >
     *      All notifications, regardless of their origin, are summed up in
     *      the counter of the Notification Center's glyph.
     *      When clicked, a list is shown with all notifying services.
     *      The entries each consist of the services' respective glyph, counter
     *      and title.
     *      Entries as well can be clicked; the user is then directed to the
     *      view of the service.
     *
     *   rivals:
     *     Awareness Tool: >
     *        2do
     *
     * rules:
     *   usage:
     *     1: The Notification Center MUST be unique for the page.
     *     2: The Notification Center MUST be in the metabar.
     *
	* ----
	*
	* @return  \ILIAS\UI\Component\MainControls\Prompts\NotificationCenter
	*/
	public function notificationCenter();

     /**
      * ---
     * description:
     *   purpose: >
     *     The Awareness Tool brings system-events to the user's attention.
     *     It opens a popover when clicked, giving further information for the
     *     notification(s).
     *     The contained components of the pop-over are hardly limited - they
     *     may list notifications as well as giving navigational options
     *     or direct functionality like e.g. answering a chat-request.
     *
     *   composition: >
     *      The Notification Center is visualized as a glyph with a status counter.
     *
     *   effect: >
     *      When the glyph is clicked, a popover expands and gives access to
     *      the notifying service(s).
     *
     *   rivals:
     *     Notification Center: >
     *        The Notification Center is for permament notes, such as mails in
     *        the inbox. Also, the contents are limited to more counter-glyphs
     *        which will change the context when operated.
     *
     * rules:
     *   usage:
     *     1: There MUST be but one Awareness Tool on the page.
     *     2: The Awareness Tool MUST be in the metabar.
     *---
     *
     * @return  \ILIAS\UI\Component\MainControls\Prompts\AwarenessTool
     */
     public function awarenessTool();

}
