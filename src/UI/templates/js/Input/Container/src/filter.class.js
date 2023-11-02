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

import FilterField from './filterfield.class';

/**
 * @type {string}
 */
const CSSCLASS_INPUTSECTION = '.il-filter-input-section';

/**
 * @type {string}
 */
const CSSCLASS_COLLAPSEDSECTION = '.il-filter-inputs-active';

/**
 * @type {string}
 */
const CSSCLASS_FIELD_WRAPPER = '.il-filter__input';

/**
 * @type {string}
 */
const QUERYSELECTOR_EXPANDCONTROLS = '.il-filter-bar-opener > button';

export default class Filter {
  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {HTMLDivElement}
   */
  #inputSection;

  /**
   * @type {HTMLDivElement}
   */
  #collapsedRepresentationSection;

  /**
   * @type {array} FilterField
   */
  #usrInputs;

  /**
   * @type {
   *  expand: HTMLButtonElement,
   *  collapse: HTMLButtonElement,
   *  inputExpand: HTMLInputElement
   * }
   */
  #controls;

  /**
   * @param {string} containerId
   * @param {bool} expanded
   * @param {bool} active
   * @throws {Error} if DOM element is missing
   */
  constructor(containerId, expanded, active) {
    this.#component = document.getElementById(containerId);
    if (this.#component === null) {
      throw new Error(`Could not find a Filter for id '${containerId}'.`);
    }
    this.#inputSection = this.#component.querySelector(CSSCLASS_INPUTSECTION);
    this.#collapsedRepresentationSection = this.#component.querySelector(CSSCLASS_COLLAPSEDSECTION);

    // the actual user inputs:
    this.#usrInputs = [];
    const usrInputsFields = this.#inputSection.querySelectorAll(CSSCLASS_FIELD_WRAPPER);
    const usrInputsStates = this.#inputSection.querySelectorAll('input[name*="filter_status_"]');
    for (let i = 0; i < usrInputsFields.length; i += 1) {
      this.#usrInputs[i] = new FilterField(usrInputsFields[i], usrInputsStates[i]);
    }

    // general filter controls
    const expandControls = this.#component.querySelectorAll(QUERYSELECTOR_EXPANDCONTROLS);
    this.#controls = {
      expand: expandControls.item(0),
      collapse: expandControls.item(1),
      inputExpand: this.#inputSection.querySelector('input[name$="/filter_all_expanded"]'),
    };
    this.#controls.expand.addEventListener('click', (e) => this.#expandHandler(e, true));
    this.#controls.collapse.addEventListener('click', (e) => this.#expandHandler(e, false));

    window.top.console.log(this.#controls);
    /*
    this.#activeControls = {
      input: null,
      // this.#component.querySelector('.il-filter-bar-toggle > button')
    };

    const user_inputs = {};
    this.#inputSection.querySelectorAll('.il-filter__input > label').forEach(
      function(label) {
        let input = document.getElementById(label.htmlFor);
        user_inputs[input.name] = input;
      }
    );

    const inputs = {};
    this.#inputSection.querySelectorAll('input').forEach(
      (i) => {
        if (i.name.endsWith('filter_status_active')) {
          inputs.filter_active_status = i;
        }
        if (i.name.endsWith('filter_status_expanded')) {
          inputs[i.name] = i;
          inputs.filter_expanded_status = i;
        }
        //window.top.console.log(i);
      },

    );
    this.#activeControls.input = inputs.filter_active_status;
    this.#expandControls.input = inputs.filter_expanded_status;

    window.top.x = this.#inputSection;
    this.activate(active);
*/
    this.#expand(expanded);
  }

  addFilterField() {
    // filterField
    // statusField
  }

  resetAll() {
    // filterFields
  }

  /**
   * @param {bool} active
   * @return {void}
   */
  activate(flag) {
    // statusField
    // activeControl

  }

  /**
   * @param {bool} active
   * @return {void}
   */
  #expand(flag) {
    this.#inputSection.style.display = flag ? 'flex' : 'none';
    this.#collapsedRepresentationSection.style.display = flag ? 'none' : 'flex';
    this.#controls.expand.style.display = flag ? 'none' : 'inline-flex';
    this.#controls.collapse.style.display = flag ? 'inline-flex' : 'none';
    this.#controls.inputExpand.value = flag ? 1 : 0;
  }

  /**
   * @param {ClickEvent} event
   * @param {bool} active
   * @return {bool}
   */
  #expandHandler(event, flag) {
    event.preventDefault();
    this.#expand(flag);
    return false;
  }
}
