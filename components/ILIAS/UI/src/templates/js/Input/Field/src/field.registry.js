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

import TextField from './text.field.class';
import NumericField from './numeric.field.class';
import GroupField from './group.field.class';

const CLASSES = {
  Text: TextField,
  Numeric: NumericField,
  Group: GroupField,
};

export default class FieldRegistry {
  /**
   * @type {Array<string, Field>}
   */
  #instances = [];

  /**
   * @param {string} componentId
   * @param {string} componentType
   * @throws {Error} if the component was already registered
   */
  register(componentId, componentType) {
    if (this.#instances[componentId] !== undefined) {
      throw new Error(`Field with id '${componentId}' has already been registered.`);
    }
    window.top.console.log('register: ' + componentType);
    this.#instances[componentId] = new CLASSES[componentType](componentId);
    window.top.console.log(this.#instances);
  }

  getValues() {
    const r = {};
    Object.keys(this.#instances).forEach(
      (id) => { r[id] = this.#instances[id].getValue(); },
    );
    return r;
  }

  /**
   * @param {string} componentId
   * @param {string} componentType
   * @throws {Error} if the component was already registered
   */
  getValueRepresentation(componentId) {
    return this.#instances[componentId].getValueRepresentation();
  }
}
