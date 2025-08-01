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
const SETTINGS = '.c-filter__settings .glyph';
const FILTER = '.c-filter__filter';
const TOGGLE_FIELD = '.c-filter__filter-inputs input[name="filter/__toggle"]';
const EXPAND_FIELD = '.c-filter__filter-inputs input[name="filter/__expand"]';
const INACTIVE_FILTERS_FIELD = '.c-filter__filter-inputs input[name="filter/__inactive"]';
const SINGLE_CONTROL = '.c-filter__filter--control';
// const INACTIVE_FILTERS = '.c-filter__filter-diabled_inputs template';
const INACTIVE_FILTERS = '.c-filter__filter-disabled_inputs';

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
   * @type {HTMLInputElement}
   */
  #inactiveFiltersField;

  /**
   * @type {HTMLDivElement}
   */
  #singleFilterControls;

  /**
   * @type {HTMLDialogElement}
   */
  #inactiveFilters;

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
    this.#inactiveFiltersField = component.querySelector(INACTIVE_FILTERS_FIELD);
    this.#singleFilterControls = component.querySelectorAll(SINGLE_CONTROL);
    this.#inactiveFilters = component.querySelector(INACTIVE_FILTERS);

    this.#singleFilterControls.forEach(
      (sfc) => {
        const glyph = sfc.querySelector('.glyph');
        glyph.addEventListener('click', () => this.toggleSingleActivation(sfc.parentNode));
      },
    );

    component.querySelector(SETTINGS).addEventListener('click', () => this.showSettings(true));
    component.querySelector(`${INACTIVE_FILTERS} .modal-content .modal-header button`).addEventListener('click', () => this.showSettings(false));
  }

  /**
   * @param {HTMLDivElement} filterInput
   */
  toggleSingleActivation(filterInput) {
    // if (filterInput.parentNode instanceof HTMLTemplateElement) {
    if (filterInput.parentNode.classList.contains('c-filter__filter-disabled_inputs')) {
      this.#filterInputs.appendChild(filterInput);
    } else {
      this.#inactiveFilters.appendChild(filterInput);
    }

    const inactive = [];
    this.#inactiveFilters.querySelectorAll(`${FILTER}--input`).forEach(
      (f) => {
        inactive.push(f.dataset.filterkey);
      },
    );
    this.#inactiveFiltersField.value = inactive.join(':');
  }

  /**
   * @param {bool} flag
   */
  showSettings(flag) {
    if (flag) {
      this.#inactiveFilters.showModal();
    } else {
      this.#inactiveFilters.close();
    }
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
