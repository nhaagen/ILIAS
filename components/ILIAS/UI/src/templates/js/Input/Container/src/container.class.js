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

import FormNode from './formnode.class';

const ROOT = 'root';

export default class Container {
  /**
   * @type {HTMLElement}
   */
  #component;

  #nodes;

  /**
   * @param {HTMLElement} id
   * @param {string} type
   */
  constructor(component) {
    this.#component = component;
    this.#nodes = new FormNode(ROOT);
    this.#buildTree();
  }

  #buildTree() {
    const search = '[name]';
    const fields = this.#component.querySelectorAll(search);

    fields.forEach((field) => {
      console.log(`field: ${field.name}`);
      this.#register(this.#nodes, field.name.split('/'), field);
    });
  }

  #register(pointer, nameparts, component) {
    let current = pointer;

    const part = nameparts.shift();
    if (!current.getNodeNames().includes(part)) {
      current.addNode(new FormNode(part));
    }
    current = current.getNodeByName(part);

    if (nameparts.length > 0) {
      this.#register(current, nameparts, component);
    } else {
      current.addField(component);
    }
  }

  nodes() {
    return this.#nodes.getNodeByName(this.#nodes.getNodeNames().shift());
  }

  getValues(fieldName) {
    //return this.nodes().getValuesRecursively();
    return this.nodes().getValuesFlat();
    // return this.getValuesRecursivley([], this.nodes());

    /*
    let node = this.#nodes;
    const parts = fieldName.split('/');
    parts.forEach((part) => node = node.getNodeByName(part));
    let values = node.getFields().map(
      (field) => field.value
    );

    return values;
    */
  }
  /*
  getValuesRecursively(values, node) {
    values[node.getName()] = node.getValues();

    if (node.getNodeNames().length > 0) {
      node.getNodeNames().forEach(
        (n) => this.getValuesRecursively(values[node.getName()], node.getNodeByName(n)),
      );
    }
    return values;
  }
  */
}
