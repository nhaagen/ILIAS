/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

export default class Popover {
  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {array}
   */
  #options;

  /**
   * @type {string}
   */
  #anchorId;

  /**
   * @param {HTMLDivElement} component
   */
  constructor(component, options) {
    this.#component = component;
    this.#options = options;
    this.#anchorId = component.getAttribute('anchor');
    this.#component.style.positionAnchor = this.#anchorId;

    // move to CSS
    this.#component.style.margin = 0;
    this.#component.style.padding = 0;
  }

  /**
   * @param signalData signalData
   * @return void
   */
  showPopover(signalData) {
    const anchor = signalData.triggerer.get(0);
    this.#show(anchor);
  }

  /**
   * @param signalData signalData
   * @return void
   */
  replaceContentFromSignal(signalData) {
    const { url } = signalData.options;
    this.#replaceContent(url);
  }

  /**
   * @param {string} url
   * @return void
   */
  #replaceContent(url) {
    const id = `content_${this.#component.getAttribute('id')}`;
    il.UI.core.replaceContent(id, url, 'content');
  }

  /**
   * @param {HTMLElement} anchor
   * @return void
   */
  #show(anchor) {
    anchor.style.anchorName = this.#anchorId;
    // options/css ?
    this.#component.style.top = `anchor(${this.#anchorId} bottom)`;
    this.#component.style.left = `anchor(${this.#anchorId} right)`;

    if (this.#options.url) {
      this.#replaceContent(this.#options.url);
    }

    this.#component.showPopover();
    this.#position(anchor);
  }
  
  /**
   * @param {HTMLElement} anchor
   * @return void
   */
  #position(anchor) {
    console.log(this.#options);
    /*
    const offset = this.#component.offsetHeight / -2;
    this.#component.style.marginTop = `${offset}px)`;
    */
  }
}
