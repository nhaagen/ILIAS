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
    console.log(this.#options);
    const cWidth = this.#component.offsetWidth;
    const cHeight = this.#component.offsetHeight;
    
    const aWidth = anchor.offsetWidth;
    const aHeight = anchor.offsetHeight;


    const placement = this.#options.placement;
    const aRect = anchor.getBoundingClientRect();
    const pRect = this.#component.getBoundingClientRect();
    const dpRect = this.getDisplayRect();

    
    console.log(aRect);
    console.log(pRect);

    // move to CSS
    this.#component.style.margin = 0;
    this.#component.style.padding = 0;
    this.#component.style.position = 'absolute';
    


    if(placement === 'horizontal') {
      const anchorMiddleVert = aRect.top  + aRect.height / 2;
      this.#component.style.top = anchorMiddleVert - (pRect.height / 2)+ 'px';
    
    } else {
      const anchorMiddleHor = aRect.left  + aRect.width / 2;
      console.log(anchorMiddleHor);
      this.#component.style.left = anchorMiddleHor - (pRect.width / 2) + 'px';

    }






    //this.checkVerticalBounds();
    //this.checkHorizontalBounds();
    /*
    this.#component.style.marginTop = `${offset}px)`;
    */
  }

/**
   * @returns {undefined}
   */
  checkVerticalBounds() {
    const ttRect = this.#component.getBoundingClientRect();
    const dpRect = this.getDisplayRect();

    if (ttRect.bottom > (dpRect.top + dpRect.height)) {
      this.#component.classList.add('c-tooltip--top');
      //this.#container.classList.add('c-tooltip--top');
    }
  }

  /**
   * @returns {undefined}
   */
  checkHorizontalBounds() {
    const ttRect = this.#component.getBoundingClientRect();
    const dpRect = this.getDisplayRect();

    if ((dpRect.width - dpRect.left) < ttRect.right) {
      this.#component.style.transform = `translateX(${(dpRect.width - dpRect.left) - ttRect.right}px)`;
    }
    if (ttRect.left < dpRect.left) {
      this.#component.style.transform = `translateX(${(dpRect.left - ttRect.left) - ttRect.width / 2}px)`;
    }
  }
  /**
   * @returns {{left: number, top: number, width: number, height: number}}
   */
  getDisplayRect() {
    return  document.getElementsByTagName('main')[0].getBoundingClientRect();
    /*if (this.#main !== null) {
      //return this.#main.getBoundingClientRect();
    }
    */

    return {
      left: 0,
      top: 0,
      width: window.innerWidth,
      height: window.innerHeight,
    };
  }

}
