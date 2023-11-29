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

/**
 * @type {number}
 */
const MODE_11 = 1;

/**
 * @type {number}
 */
const MODE_nn = 2;

export default class MatchingQuestion {
  /**
   * @type {HTMLDivElement}
   */
  #container;

  /**
   * @type {number}
   */
  #mode;

  /**
   * @type {NodeList}
   */
  #draggables;

  /**
   * @type {NodeList}
   */
  #droppables;

  /**
   * @type {HTMLDivElement}
   */
  #sourceArea;

  /**
   * @param {string} containerId
   * @param {number} mode
   * @throws {Error} if DOM element is missing
   */
  constructor(containerId, mode) {
    this.#container = document.getElementById(containerId);
    if (this.#container === null) {
      throw new Error(`Could not find a mathcin MatchingQuestion container for id '${containerId}'.`);
    }
    if (mode !== MODE_11 && mode !== MODE_nn) {
      throw new Error('MatchingQuestion with invalid mode');
    }
    this.#mode = mode;

    this.#draggables = document.querySelectorAll('.draggable');
    this.#droppables = document.querySelectorAll('.droparea');
    this.#sourceArea = document.querySelector('#sourceArea');

    this.#initEvents();
  }

  #initEvents() {
    this.#draggables.forEach(
      (item) => {
        item.addEventListener('dragstart', (e) => this.#handleDragStart(e));
        item.addEventListener('dragend', (e) => this.#handleDragEnd(e));
        // item.addEventListener('dragover', (e)=>this.#handleDragOver(e));

        // item.addEventListener('touchstart', (e)=>this.#handleDragStart(e));
        // item.addEventListener('touchend', (e)=>this.#handleDragEnd(e));
        // item.addEventListener('touchleave', (e)=>this.#handleDragEnd(e));
        item.addEventListener('touchmove', (e) => this.#handleDragStart(e));
        // item.addEventListener('touchcancel', (e)=>this.#handleDragStart(e));
      },
    );
    this.#droppables.forEach(
      (item) => {
        item.addEventListener('dragover', (e) => this.#handleDragOver(e, item));
        // item.addEventListener('dragenter', (e)=>this.#handleDragEnter(e, item));
        item.addEventListener('dragleave', (e) => this.#handleDragLeave(e, item));
        item.addEventListener('drop', (e) => this.#handleDrop(e, item));
      },
    );
    const sourceArea = this.#sourceArea;
    this.#sourceArea.addEventListener('dragover', (e) => this.#handleDragOver(e, sourceArea));
    this.#sourceArea.addEventListener('drop', (e) => this.#handleDropBack(e));
  }

  /**
   * @return {void}
   */
  #highlightDroppables() {
    this.#droppables.forEach((item) => item.classList.add('droppableTarget'));
  }

  /**
   * @return {void}
   */
  #downlightDroppables() {
    this.#droppables.forEach((item) => item.classList.remove('droppableTarget'));
  }

  /**
   * @param {HTMLDivElement} item
   * @return {void}
   */
  #highlightCurrentDrop(item) {
    item.classList.add('droppableHover');
  }

  /**
   * @param {HTMLDivElement} item
   * @return {void}
   */
  #downlightCurrentDrop(item) {
    item.classList.remove('droppableHover');
  }

  /**
   * @param {DragEvent} e
   * @return {void}
   */
  #handleDragStart(e) {
    console.log('START');
    this.#highlightDroppables();
    // console.log(e);
    // console.log(this);
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text', e.target.id);
  }

  /**
   * @param {DragEvent} e
   * @return {void}
   */
  #handleDragEnd(e) {
    console.log('END');
    // console.log(e);
    this.#downlightDroppables();
  }

  /**
   * @param {DragEvent} e
   * @return {void}
  #handleDragEnter(e, area) {
    //e.preventDefault();
    this.#highlightCurrentDrop(area);
  }
   */

  /**
   * @param {HTMLDivElement} area
   * @param {DragEvent} e
   * @return {void}
   */
  #handleDragOver(e, area) {
    e.preventDefault();
    this.#highlightCurrentDrop(area);
    // return false;
  }

  /**
   * @param {DragEvent} e
   * @param {HTMLDivElement} area
   * @return {void}
   */
  #handleDragLeave(e, area) {
    this.#downlightCurrentDrop(area);
    // return false;
  }

  /**
   * @param {DragEvent} e
   * @param {HTMLDivElement} area
   * @return {void}
   */
  #handleDrop(e, area) {
    e.preventDefault();
    console.group('DROP');
    console.log(e);
    console.log(area);

    this.#downlightCurrentDrop(area);

    console.log(e.dataTransfer.effectAllowed);
    console.log(e.dataTransfer.getData('text'));

    const data = e.dataTransfer.getData('text');
    const source = document.getElementById(data);

    const clone = source;
    if (!clone.hasAttribute('data-clone')) {
      const clone = source.cloneNode(true);
      clone.id += '__clone';
      clone.setAttribute('data-clone', true);
    }
    clone.addEventListener('dragstart', (e) => this.#handleDragStart(e));

    const target = area.querySelector('.ilMatchingQuestionTerm');
    target.appendChild(clone);

    console.groupEnd();
    // e.stopPropagation();
    // return false;
  }

  /**
   * @param {DragEvent} e
   * @return {void}
   */
  #handleDropBack(e) {
    e.preventDefault();
    console.group('DROP BACK');
    console.log(e);

    // this.#downlightCurrentDrop(area);

    console.log(e.dataTransfer.effectAllowed);
    console.log(e.dataTransfer.getData('text'));

    const data = e.dataTransfer.getData('text');
    const source = document.getElementById(data);
    this.#sourceArea.appendChild(source);
    // source.parentNode.removeChild(source);

    console.groupEnd();
    // e.stopPropagation();
    // return false;
  }
}
