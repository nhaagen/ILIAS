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

import FormNode from './formnode.class.js';

/**
 * @type {string}
 */
// const SEARCH_FORMNODE = '[name]';
const SEARCH_FORMNODE = '[data-il-ui-type]';
const SEARCH_FIELD = '[name]';

export default class Container {
  /**
   * @type {HTMLElement}
   */
  #container;

  /**
   * @type {FormNode}
   */
  #nodes;

  /**
   * @param {HTMLElement} container
   * @return {void}
   */
  constructor(container) {
    this.#container = container;
    this.#nodes = new FormNode('form', 'FormContainerInput');

    const ilTopInputDomElements = Array.from(
      container.querySelectorAll(SEARCH_FORMNODE),
    )
      .filter((element) => !element.parentNode.closest(SEARCH_FORMNODE));

    ilTopInputDomElements.forEach(
      (topInputDomElement) => this.#register(topInputDomElement, this.#nodes),
    );
  }

  #register(outerDomNode, node) {
    const label = this.#getLabel(outerDomNode);
    const nuNode = new FormNode(
      label,
      outerDomNode.getAttribute('data-il-ui-type'),
    );

    const inputFields = this.#getInputFields(outerDomNode);
    inputFields.forEach(
      (field) => nuNode.addHtmlField(field),
    );

    const ilUIFormNodes = this.#getIlUIFormNodes(outerDomNode);
    ilUIFormNodes.forEach(
      (domNode) => this.#register(domNode, nuNode),
    );
    node.addChildNode(nuNode);
  }

  #getIlUIFormNodes(outerDomNode) {
    return Array.from(
      outerDomNode.querySelectorAll(SEARCH_FORMNODE),
    ).filter((element) => element.parentNode.closest(SEARCH_FORMNODE) === outerDomNode);
  }

  #getInputFields(outerDomNode) {
    return Array.from(
      outerDomNode.querySelectorAll(SEARCH_FIELD),
    ).filter((element) => element.parentNode.closest(SEARCH_FORMNODE) === outerDomNode);
  }

  #getLabel(outerDomNode) {
    let label = '';
    const labelNode = Array.from(
      outerDomNode.querySelectorAll('label'),
    ).filter((element) => element.parentNode.closest(SEARCH_FORMNODE) === outerDomNode);
    if (labelNode.length > 0) {
      label = labelNode[0].textContent;
    }
    return label;
  }

  getNodes() {
    return this.#nodes;
  }

  getAllNodesFlat(initNode, initOut) {
    let out = initOut;
    let node = initNode;
    if (!out) {
      out = [];
      node = this.#nodes;
    }

    out.push(
      {
        label: node.getLabel(),
        values: node.getValues(),
        valuesRepresentation: this.#getValueRepresentation(node),
        type: node.getType(),
      },
    );
    const children = node.getFilteredChildren();
    if (children.length > 0) {
      children.forEach(
        (child) => this.getAllNodesFlat(child, out),
      );
    }
    return out;
  }

  getAllNodesStruct(initNode) {
    let node = initNode;
    if (!node) {
      node = this.#nodes;
    }
    const entry = {
      label: node.getLabel(),
      values: node.getValues(),
      valuesRepresentation: this.#getValueRepresentation(node),
      type: node.getType(),
      fields: node.getHtmlFields(),
      children: [],
    };

    // let children = node.getChildren();
    const children = node.getFilteredChildren();
    if (children.length > 0) {
      children.forEach(
        (child) => entry.children.push(this.getAllNodesStruct(child)),
      );
    }
    return entry;
  }

  #getValueRepresentation(node) {
    this.tempInit();

    if (this.presentation[node.getType()]) {
      return this.presentation[node.getType()](node);
    }
    return node.getValues();
  }

  presentation = [];

  tempInit() {
    this.presentation.PasswordFieldInput = () => null;

    this.presentation.SwitchableGroupFieldInput = function (node) {
      const children = node.getFilteredChildren();
      if (children.length === 0) {
        return [];
      }
      const representation = [];
      children.forEach((child) => representation.push(child.getLabel()));
      return representation;
    };

    this.presentation.RadioFieldInput = function (node) {
      const checked = node.getHtmlFields().filter((element) => element.checked);
      if (checked.length === 0) {
        return [];
      }
      const representation = [];
      checked.forEach(
        (field) => representation.push(
          field.parentNode.querySelector('label').textContent,
        ),
      );
      return representation;
    };

    this.presentation.MultiSelectFieldInput = this.presentation.RadioFieldInput;

    this.presentation.DurationFieldInput = function (node) {
      const [start, end] = node.getChildren().map((child) => child.getValues()[0]);
      if (start && end) {
        return `${start} - ${end}`;
      }
      return '-';
    };

    this.presentation.LinkFieldInput = function (node) {
      const [label, url] = node.getChildren().map((child) => child.getValues()[0]);
      return `${label} [${url}]`;
    };

    this.presentation.SelectFieldInput = function (node) {
      const field = node.getHtmlFields()[0];
      return field.options[field.options.selectedIndex].text;
    };
  }
}
