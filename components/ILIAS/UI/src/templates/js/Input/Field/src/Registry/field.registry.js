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

import GenericField from './generic.field.class';
import MultiSelectField from './multiselect.field.class';
import GroupField from './group.field.class';



export default class FieldRegistry {
  
  types = { // dynamisch!
    GenericField,
    GroupField,
    MultiSelectField,
  };

  /**
   * @type {Array<string, Field>}
   */
  #instances = [];

  /**
   * @param {string} componentId
   * @param {Field} component
   * @throws {Error} if the component was already registered
   */
  register(componentId, component) {
    if (this.#instances[componentId] !== undefined) {
      throw new Error(`Field with id '${componentId}' has already been registered.`);
    }
    this.#instances[componentId] = component;
    window.top.console.log('register: ' + componentId);
  }

  get(componentId) {
    return this.#instances[componentId];
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
