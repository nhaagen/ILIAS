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

import PreviewRenderer from './preview.renderer.js';
import Markdown from './markdown.class.js';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class MarkdownFactory {
  /**
     * @type {Array<string, Markdown>}
     */
  instances = [];

  /** @type {JQueryEventDispatcher} */
  #jqueryEventDispatcher;

  /** @type {Document} */
  #document;

  /**
   * @param {JQueryEventDispatcher} jqueryEventDispatcher
   * @param {Document} doc
   */
  constructor(jqueryEventDispatcher, doc) {
    this.#jqueryEventDispatcher = jqueryEventDispatcher;
    this.#document = doc;
  }

  /**
     * @param {string} inputId
     * @param {string} previewURL
     * @param {string} parameterName
     * @param {string} mustacheVarSignal
     * @param {string} mustacheVarSignalOption
     * @return {void}
     * @throws {Error} if the input was already initialized.
     */
  init(inputId, previewURL, parameterName, mustacheVarSignal, mustacheVarSignalOption) {
    if (undefined !== this.instances[inputId]) {
      throw new Error(`Markdown with input-id '${inputId}' has already been initialized.`);
    }

    this.instances[inputId] = new Markdown(
      new PreviewRenderer(parameterName, previewURL),
      inputId,
      mustacheVarSignal,
      mustacheVarSignalOption,
      this.#jqueryEventDispatcher,
      this.#document,
    );
  }

  /**
     * @param {string} inputId
     * @param {Markdown|null}
     */
  get(inputId) {
    return this.instances[inputId] ?? null;
  }
}
