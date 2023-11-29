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

import MatchingQuestion from './matchingquestion.class';

export default class MatchingQuestionFactory {
  /**
   * @type {Array<string, MatchingQuestion>}
   */
  #instances = [];

  /**
   * @param {string} containerId
   * @param {number} mode
   * @return {void}
   * @throws {Error} if the question was already initialized.
   */
  init(containerId, mode) {
    if (this.#instances[containerId] !== undefined) {
      throw new Error(`MatchingQuestion with id '${containerId}' has already been initialized.`);
    }
    this.#instances[containerId] = new MatchingQuestion(containerId, mode);
  }

  /**
   * @param {string} containerId
   * @return {MatchingQuestion|null}
   */
  get(containerId) {
    return this.#instances[containerId] ?? null;
  }
}
