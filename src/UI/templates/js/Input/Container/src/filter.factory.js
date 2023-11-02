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

import Filter from './filter.class';

export default class FilterFactory {
  /**
   * @type {Array<string, Filter>}
   */
  #instances = [];

  /**
   * @param {string} componentId
   * @param {bool} expanded
   * @param {bool} active
   * @return {void}
   * @throws {Error} if the filter was already initialized.
   */
  init(componentId, expanded, active) {
    if (this.#instances[componentId] !== undefined) {
      throw new Error(`Filter with id '${componentId}' has already been initialized.`);
    }
    this.#instances[componentId] = new Filter(componentId, expanded, active);
  }

  /**
   * @param {string} componentId
   * @return {Filter|null}
   */
  get(componentId) {
    return this.#instances[componentId] ?? null;
  }
}
