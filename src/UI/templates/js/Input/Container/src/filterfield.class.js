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
 * @type {string}
 */
const QUERYSELECTOR_DEACTIVATE_TRIGGER = '.input-group-addon > a.glyph';

export default class FilterField {
  /**
   * @type {HTMLDivElement}
   */
  #field;

  /**
   * @type {HTMLInputElement}
   */
  #stateInput;

  /**
   * @param {HTMLDivElement} field
   * @param {HTMLInputElement} stateInput
   */
  constructor(field, stateInput) {
    this.#field = field;
    this.#stateInput = stateInput;

    field.querySelector(QUERYSELECTOR_DEACTIVATE_TRIGGER).addEventListener(
      'click',
      () => this.#deactivate(),
    );
  }

  /**
   * @return void
   */
  #activate() {
    this.#stateInput.value = 1;
    this.#field.style.display = 'inline-flex';
  }

  /**
   * @return void
   */
  #deactivate() {
    this.#stateInput.value = 0;
    this.#field.style.display = 'none';
  }
}
