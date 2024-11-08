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

const EXPANDER = '.c-filter__expander-expand';
const COLLAPSER = '.c-filter__expander-collapse';
const FILTER = '.c-filter__filter';
const TOGGLE_FIELD = '.c-filter__filter-inputs input[name="filter/__toggle"]';
const EXPAND_FIELD = '.c-filter__filter-inputs input[name="filter/__expand"]';

export default class Filter {
  /**
   * @type {HTMLFormElement}
   */
  #component;

  /**
   * @type {HTMLSpanElement}
   */
  #expander;

  /**
   * @type {HTMLSpanElement}
   */
  #collapser;

  /**
   * @type {HTMLDivElement}
   */
  #filterInputs;

  /**
   * @type {HTMLDivElement}
   */
  #filterMinValues;

  /**
   * @type {HTMLInputElement}
   */
  #filterToggleField;

  /**
   * @type {HTMLInputElement}
   */
  #filterExpandField;

  /**
   * @param {HTMLFormElement} component
   */
  constructor(component) {
    this.#component = component;
    this.#expander = component.querySelector(EXPANDER);
    this.#collapser = component.querySelector(COLLAPSER);
    // this.#filterArea = component.querySelector(FILTER);
    this.#filterInputs = component.querySelector(`${FILTER}-inputs`);
    this.#filterMinValues = component.querySelector(`${FILTER}-shortvalues`);
    this.#filterToggleField = component.querySelector(TOGGLE_FIELD);
    this.#filterExpandField = component.querySelector(EXPAND_FIELD);
  }

  /**
   * @param {bool} flag
   */
  toggle(flag) {
    this.#filterToggleField.value = flag;
    this.#submit();
  }

  /**
   * @param {bool} flag
   */
  toggleExpand(flag) {
    if (flag) {
      this.#expand();
    } else {
      this.#collapse();
    }
  }

  #collapse() {
    this.#collapser.classList.add('hidden');
    this.#expander.classList.remove('hidden');
    this.#filterInputs.classList.add('hidden');
    this.#filterMinValues.classList.remove('hidden');
    this.#filterExpandField.value = false;
  }

  #expand() {
    this.#collapser.classList.remove('hidden');
    this.#expander.classList.add('hidden');
    this.#filterInputs.classList.remove('hidden');
    this.#filterMinValues.classList.add('hidden');
    this.#filterExpandField.value = true;
  }

  #submit() {
    this.#component.submit();
  }
}
