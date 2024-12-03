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
    if (this.#options.url) {
      this.#replaceContent(this.#options.url);
    }
    this.#component.showPopover();
    this.#position(anchor);
    //this.#component.ownerDocument.querySelector('main').addEventListener('scroll', (e)=>this.#position(anchor));
    
  }

  /**
   * @param {HTMLElement} anchor
   * @return void
   */
  #position(anchor) {
    /*
    anchor.style.anchorName = this.#anchorId;
    this.#component.style.positionAnchor = this.#anchorId;
    this.#component.style.top = `anchor(${this.#anchorId} bottom)`;
    this.#component.style.left = `anchor(${this.#anchorId} right)`;
    */
    const { placement } = this.#options;
    const anchorRect = anchor.getBoundingClientRect();
    const componentRect = this.#component.getBoundingClientRect();
    const displayRect = this.#getDisplayRect();

    if (placement === 'horizontal') {
      const anchorMiddleVert = anchorRect.top + (anchorRect.height / 2);
      this.#component.style.top = `${anchorMiddleVert - (componentRect.height / 2)}px`;

      if(anchorRect.left + componentRect.width > displayRect.width) {
        this.#component.classList.add('c-popover--left');
        this.#component.style.right = `${anchorRect.left}px`;
      } else {
        this.#component.classList.add('c-popover--right');
        this.#component.style.left = `${anchorRect.right}px`;
      }

    } else {

      const anchorMiddleHor = anchorRect.left + (anchorRect.width / 2);
      this.#component.style.left = `${anchorMiddleHor - (componentRect.width / 2)}px`;

      if(anchorRect.top - componentRect.height < 0) {
        this.#component.classList.add('c-popover--bottom');
        this.#component.style.top = `${anchorRect.bottom}px`;
      } else {
        this.#component.classList.add('c-popover--top');
        this.#component.style.top = `${anchorRect.top - componentRect.height}px`;
      }
    }

  }

  /**
   * @returns {DOMRect}
   */
  #getDisplayRect() {
    const mainElements = this.#component.ownerDocument.getElementsByTagName('main');
    const visibleMain = Array.from(mainElements).find(
      (element) => Object.prototype.hasOwnProperty.call(element, 'hidden') === false,
    );
    return visibleMain.getBoundingClientRect();
  }
}
