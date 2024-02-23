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

export default class FormNode {
  #name;

  #nodes;

  #fields;

  constructor(name) {
    this.#name = name;
    this.#nodes = [];
    this.#fields = [];
  }

  addNode(node) {
    this.#nodes[node.getName()] = node;
  }

  addField(field) {
    this.#fields.push(field);
  }

  getName() {
    return this.#name;
  }

  getNodes() {
    return this.#nodes;
  }

  getNodeNames() {
    return Object.keys(this.#nodes);
  }

  getNodeByName(name) {
    return this.#nodes[name];
  }

  getFields() {
    return this.#fields;
  }

  getValues() {
    const values = [];

    this.#fields.forEach(
      (field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
          if (field.checked) {
            values.push(field.value);
          }
        } else {
          values.push(field.value);
        }
      },
    );

    return values;
  }


  #filteredSubnodes() {
    let subnodes = this.getNodeNames();

    // optional groups:
    if (this.getFields().length > 0 && this.getValues().length === 0) {
      subnodes = []; // or, equally: return values;
    }
    // switchable groups
    if (this.getFields().length > 0
      && this.getFields().filter((f) => f.type === 'radio').length === this.getFields().length
    ) {
      subnodes = [];
      const index = this.getFields().findIndex((f) => f.value === this.getValues().shift());
      if (this.getNodeNames().length > index && index > -1) {
        subnodes = [this.getNodeNames()[index]];
      }
    }
    return subnodes;
  }

  getValuesRecursively(initValues) {
    const values = initValues || [];

    values[this.getName()] = this.getValues();

    let subnodes = this.#filteredSubnodes();
    subnodes.forEach(
      (n) => this.getNodeByName(n).getValuesRecursively(values[this.getName()]),
    );
    return values;
  }

  getValuesFlat(initValues, initName) {
    const values = initValues || [];
    const name = initName || [];

    name.push(this.getName());
    values[name.join('/')] = this.getValues();

    let subnodes = this.#filteredSubnodes();
    subnodes.forEach(
      (n) => this.getNodeByName(n).getValuesFlat(values, name)
    );
    return values;
  }

}
