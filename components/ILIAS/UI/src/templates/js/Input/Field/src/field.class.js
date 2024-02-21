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

export default class Field {
  /**
   * @type {HTMLElement}
   */
  component;

  /**
   * @param {string} id
   */
  constructor(componentId) {
    const search = `[name="${componentId}"]`;
    this.component = document.querySelector(search);
    window.top.console.log(search);
    window.top.console.log(this.component);
  }

  /**
   * @return {mixed}
   */
  getValue() {
     return this.component.value;
  }

  /**
   * @return {HTMLDivElement}
   */
  getValueRepresentation() {
     return `<div><b>${this.getValue()}</b></div>`;
  }
}
