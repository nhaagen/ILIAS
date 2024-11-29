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

import Popover from './popover.class';

export default class PopoverFactory {
  /**
   * @type {Array<string, Popover>}
   */
  #instances = [];

  /**
   * @param {string} componentId
   * @param {array} options
   * @return {void}
   * @throws {Error} if the table was already initialized.
   * @throws {Error} if the element was not found.
   */
  init(componentId, options) {
    if (this.#instances[componentId] !== undefined) {
      throw new Error(`Popover with id '${componentId}' has already been initialized.`);
    }
    const component = document.getElementById(componentId);
    if (!component) {
      throw new Error(`Cannot find element with id '${componentId}'.`);
    }
    this.#instances[componentId] = new Popover(component, options);
  }

  /**
   * @param {string} componentId
   * @return {Popover|null}
   */
  get(componentId) {
    return this.#instances[componentId] ?? null;
  }
}
