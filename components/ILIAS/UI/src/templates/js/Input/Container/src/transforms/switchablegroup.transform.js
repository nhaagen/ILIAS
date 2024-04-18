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

export default class SwitchableGroupTransforms {
  /**
   * @param {FormNode} node
   * @return {Array}
   */
  valueTransform(node) {
    const children = node.getChildren();
    if (children.length === 0) {
      return [];
    }
    const representation = [];
    children.forEach((child) => representation.push(child.getLabel()));
    return representation;
  }

  /**
   * @param {FormNode} node
   * @return {Array}
   */
  childrenTransform(node) {
    const children = [];
    const nodes = node.getAllChildren();
    node.getHtmlFields().forEach(
      (field, index) => {
        if (field.checked) {
          children.push(nodes[index]);
        }
      },
    );
    return children;
  }
}
