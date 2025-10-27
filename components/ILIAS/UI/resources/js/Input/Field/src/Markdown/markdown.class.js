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

/* global HTMLSpanElement */
/* global HTMLCollection */
/* global KeyboardEvent */

import Textarea from '../Textarea/textarea.class.js';

/**
 * @type {string}
 */
const CONTENT_WRAPPER_KEY_TEXTAREA = 'textarea';

/**
 * @type {string}
 */
const CONTENT_WRAPPER_KEY_PREVIEW = 'preview';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class Markdown extends Textarea {
  /**
     * @type {string[]}
     */
  previewHistory = [];

  /**
     * @type {PreviewRenderer}
     */
  previewRenderer;

  /**
     * @type {Map}
     */
  contentWrappers;

  /**
     * @type {HTMLButtonElement[]}
     */
  viewControls;

  /**
     * @type {HTMLButtonElement[]}
     */
  actions;

  /** @type {JQueryEventDispatcher} */
  #jqueryEventDispatcher;

  /** @type {Document} */
  #document;

  /** @type {string} */
  #mustacheVarSignalOption;

  /**
     * @param {PreviewRenderer} previewRenderer
     * @param {string} inputId
     * @param {string} mustacheVarSignal
     * @param {string} mustacheVarSignalOption
     * @param {JQueryEventDispatcher} jqueryEventDispatcher
     * @param {Document} doc
     * @throws {Error} if DOM elements are missing.
     */
  constructor(
    previewRenderer,
    inputId,
    mustacheVarSignal,
    mustacheVarSignalOption,
    jqueryEventDispatcher,
    doc,
  ) {
    super(inputId);

    const inputWrapper = this.textarea.closest('.c-field-markdown');

    if (inputWrapper === null) {
      throw new Error(`Could not find input-wrapper for input-id '${inputId}'.`);
    }

    this.previewRenderer = previewRenderer;
    this.#jqueryEventDispatcher = jqueryEventDispatcher;
    this.#document = doc;
    this.#mustacheVarSignalOption = mustacheVarSignalOption;

    this.contentWrappers = getContentWrappersOrAbort(inputWrapper);
    this.viewControls = getViewControlsOrAbort(inputWrapper);
    this.actions = getMarkdownActions(inputWrapper);

    let hasNewlineBeenInserted = true;

    this.textarea.addEventListener('keydown', (event) => {
      hasNewlineBeenInserted = this.handleEnterKeyBeforeInsertionHook(event);
    });

    this.textarea.addEventListener('keyup', (event) => {
      this.handleEnterKeyAfterInsertionHook(event, hasNewlineBeenInserted);
    });

    this.actions.forEach((action) => {
      action.addEventListener('click', (event) => {
        this.performMarkdownActionHook(event);
      });
    });

    this.viewControls.forEach((control) => {
      control.addEventListener('click', () => {
        this.toggleViewingModeHook();
      });
    });

    jqueryEventDispatcher.register(
      doc,
      mustacheVarSignal,
      (event, sigData) => this.insertMustacheVarFromSignal(event, sigData),
    );
  }

  /**
     * Automatically inserts a bullet-point or enumeration on the newly added line
     * according to the previous one.
     *
     * NOTE that this hook should only fire if the previous hook has inserted a
     * newline, otherwise this would undo the previous action.
     *
     * @param {KeyboardEvent} event
     * @param {boolean} newlineInserted
     * @return {void}
     */
  handleEnterKeyAfterInsertionHook(event, newlineInserted) {
    // skip this hook if the previous one didn't insert a newline,
    // otherwise this hook would undo the previous action.
    if (!newlineInserted || !isEnterKeyPressed(event)) {
      return;
    }

    const previousLine = this.getLinesBeforeSelection().pop();

    if (undefined !== previousLine && isBulletPointed(previousLine)) {
      this.applyTransformationToSelection(toggleBulletPoints);
      return;
    }

    if (undefined !== previousLine && isEnumerated(previousLine)) {
      this.insertSingleEnumeration();
    }
  }

  /**
     * Removes the bullet-point or enumeration of the current line if there aren't
     * any other characters.
     *
     * @param {KeyboardEvent} event
     * @return {boolean}
     */
  handleEnterKeyBeforeInsertionHook(event) {
    if (!isEnterKeyPressed(event)) {
      return false;
    }

    const currentLine = this.getLinesOfSelection().shift();

    // nothing to do if the current line is not an empty list entry.
    if (undefined === currentLine || !isEmptyListEntry(currentLine)) {
      return true;
    }

    let textBeforeSelection = this.getLinesBeforeSelection().join('\n');
    let textAfterSelection = this.getLinesAfterSelection().join('\n');

    if (textBeforeSelection.length > 0) {
      textBeforeSelection += '\n';
    }

    if (textAfterSelection.length > 0) {
      textAfterSelection = `\n${textAfterSelection}`;
    }

    this.updateTextareaContent(
      textBeforeSelection + textAfterSelection,
      this.getAbsoluteSelectionStart() - currentLine.length,
      this.getAbsoluteSelectionEnd() - currentLine.length,
    );

    // prevent newline from being added.
    event.preventDefault();
    return false;
  }

  /**
     * @param {MouseEvent} event
     * @return {void}
     */
  performMarkdownActionHook(event) {
    const markdownAction = getMarkdownActionOfButton(event.target);

    switch (markdownAction) {
      case 'insert-heading':
        this.insertCharactersAroundSelection('# ', '');
        break;
      case 'insert-link':
        this.insertCharactersAroundSelection('[', '](url)');
        break;
      case 'insert-bold':
        this.insertCharactersAroundSelection('**', '**');
        break;
      case 'insert-italic':
        this.insertCharactersAroundSelection('_', '_');
        break;
      case 'insert-bullet-points':
        this.applyTransformationToSelection(toggleBulletPoints);
        break;
      case 'insert-enumeration':
        (this.isMultilineTextSelected())
          ? this.applyTransformationToSelection(toggleEnumeration)
          : this.insertSingleEnumeration();
        break;
      case 'insert-placeholder':
        this.#jqueryEventDispatcher.dispatch(
          this.#document,
          getActionSignalOfButton(event.target),
          {},
        );
        break;

      default:
        throw new Error(`Could not perform markdown-action '${markdownAction}'.`);
    }
  }

  /**
     * @return {void}
     */
  toggleViewingModeHook() {
    this.contentWrappers.forEach((wrapper) => {
      toggleClassOfElement(wrapper, 'hidden');
    });

    this.viewControls.forEach((control) => {
      toggleClassOfElement(control, 'engaged');
    });

    // only toggle actions if they weren't disabled initially.
    if (!this.isDisabled()) {
      this.actions.forEach((action) => {
        action.disabled = !action.disabled;
        const glyph = action.querySelector('.glyph');
        if (glyph !== null) {
          toggleClassOfElement(glyph, 'disabled');
        }
      });
    }

    this.maybeUpdatePreviewContent();
  }

  /**
     * Insert a new enumeration on the current line if it's not already one.
     * All lines after that will be reindexed as long as they continue the
     * current enumeration.
     *
     * @return {void}
     */
  insertSingleEnumeration() {
    const linesOfSelection = this.getLinesOfSelection();

    // abort (refocus) if the current selection is not a single line or
    // is already enumerated.
    if (linesOfSelection.length !== 1) {
      this.textarea.focus();
      return;
    }

    const linesBeforeSelection = this.getLinesBeforeSelection();
    const lastIndex = linesBeforeSelection.length - 1;
    let previousNumber = (lastIndex >= 0)
      ? getFirstNumber(linesBeforeSelection[lastIndex]) ?? 0
      : 0;

    const newLinesOfSelection = toggleEnumeration(linesOfSelection, ++previousNumber);
    const linesAfterSelection = reindexContinuousLinesOfEnumeration(
      this.getLinesAfterSelection(),
      previousNumber,
    );

    let textBeforeSelection = linesBeforeSelection.join('\n');
    const textAfterSelection = linesAfterSelection.join('\n');
    let textOfSelection = newLinesOfSelection.join('\n');

    if (textBeforeSelection.length > 0 && textOfSelection.length > 0) {
      textBeforeSelection += '\n';
    }

    if (textOfSelection.length > 0 && textAfterSelection.length > 0) {
      textOfSelection += '\n';
    }

    const newContent = textBeforeSelection + textOfSelection + textAfterSelection;
    const characterDiff = newContent.length - this.textarea.value.length;

    // the selection should be shifted by the amount of newly added/removed
    // characters, so that the same text is still highlighted.
    this.updateTextareaContent(
      newContent,
      this.getAbsoluteSelectionStart() + characterDiff,
      this.getAbsoluteSelectionEnd() + characterDiff,
    );
  }

  /**
     * @param {function(string[]): string[]} transformation
     * @return {void}
     * @throws {Error} if the transformation does not return an array.
     *                 if the transformation is not a function.
     */
  applyTransformationToSelection(transformation) {
    if (!(transformation instanceof Function)) {
      throw new Error(`Transformation must be an instance of Function, ${typeof transformation} given.`);
    }

    const transformedSelection = transformation(this.getLinesOfSelection());

    if (!(transformedSelection instanceof Array)) {
      throw new Error(`Transformation must return an instance of Array, ${typeof transformedSelection} returned.`);
    }

    const isMultiline = (transformedSelection.length > 1);

    let textBeforeSelection = this.getLinesBeforeSelection().join('\n');
    const textAfterSelection = this.getLinesAfterSelection().join('\n');
    let textOfSelection = transformedSelection.join('\n');

    if (textBeforeSelection.length > 0 && textOfSelection.length > 0) {
      textBeforeSelection += '\n';
    }

    if (textOfSelection.length > 0 && textAfterSelection.length > 0) {
      textOfSelection += '\n';
    }

    const newContent = textBeforeSelection + textOfSelection + textAfterSelection;
    const characterDiff = newContent.length - this.textarea.value.length;

    // the new selection should hold all transformed lines if they're a
    // multiline selection. Otherwise, the selection should be shifted
    // by the amount of newly added/removed characters, so that the same
    // text is still highlighted.

    const newSelectionStart = (isMultiline)
      ? textBeforeSelection.length
      : this.getAbsoluteSelectionStart() + characterDiff;
    const newSelectionEnd = (isMultiline)
      ? newSelectionStart + textOfSelection.length - 1
      : this.getAbsoluteSelectionEnd() + characterDiff;
    this.updateTextareaContent(newContent, newSelectionStart, newSelectionEnd);
  }

  /**
     * Updates the current preview if the previously rendered content has changed.
     *
     * @return {void}
     */
  maybeUpdatePreviewContent() {
    const previousContent = this.previewHistory[(this.previewHistory.length - 1)] ?? '';
    const currentContent = this.textarea.value;

    if (currentContent === previousContent) {
      return;
    }

    this.previewHistory.push(currentContent);
    this.previewRenderer
      .getPreviewHtmlOf(currentContent).then((html) => {
        this.contentWrappers.get(CONTENT_WRAPPER_KEY_PREVIEW).innerHTML = html;
      });
  }

  /**
     * @return {function(string[]): string[]}
     */
  getBulletPointTransformation() {
    return toggleBulletPoints;
  }

  /**
     * @return {function(string[], number=1): string[]}
     */
  getEnumerationTransformation() {
    return toggleEnumeration;
  }

  /**
   * @param {MouseEvent} event
   * @param {array} sigData
   * @return {void}
   */
  insertMustacheVarFromSignal(event, sigData) {
    const value = `{{${sigData.options[this.#mustacheVarSignalOption]}}}`;
    this.insertCharactersAroundSelection(value, '');
    event.target.closest('dialog').close();
  }
}

/**
 * @param {HTMLDivElement} inputWrapper
 * @return {Map}
 * @throws {Error}
 */
function getContentWrappersOrAbort(inputWrapper) {
  const contentWrappers = new Map();

  contentWrappers.set(CONTENT_WRAPPER_KEY_TEXTAREA, inputWrapper.querySelector('textarea'));
  contentWrappers.set(CONTENT_WRAPPER_KEY_PREVIEW, inputWrapper.querySelector('.c-field-markdown__preview'));

  contentWrappers.forEach((wrapper) => {
    if (wrapper === null) {
      throw new Error('Could not find all content-wrappers for markdown-input.');
    }
  });

  return contentWrappers;
}

/**
 * @param {HTMLDivElement} inputWrapper
 * @return {HTMLButtonElement[]}
 * @throws {Error}
 */
function getViewControlsOrAbort(inputWrapper) {
  const controls = inputWrapper
    .querySelector('.il-viewcontrol-mode')
    ?.getElementsByTagName('button');

  if (!(controls instanceof HTMLCollection) || controls.length !== 2) {
    throw new Error('Could not find exactly two view-controls.');
  }

  return [...controls];
}

/**
 * @param {HTMLDivElement} inputWrapper
 * @return {HTMLButtonElement[]}
 * @throws {Error}
 */
function getMarkdownActions(inputWrapper) {
  const actions = inputWrapper
    .querySelector('.c-field-markdown__actions')
    ?.getElementsByTagName('button');

  if (actions instanceof HTMLCollection) {
    return [...actions];
  }

  return [];
}

/**
 * @param {HTMLButtonElement} button
 * @return {string|null}
 */
function getMarkdownActionOfButton(button) {
  const actionWrapper = button.closest('span[data-action]');
  if (!(actionWrapper instanceof HTMLSpanElement)) {
    return null;
  }

  if (!actionWrapper.hasAttribute('data-action')) {
    return null;
  }

  return actionWrapper.dataset.action;
}

/**
 * @param {string[]} linesAfterSelection
 * @param {number} previousNumber
 * @return {string[]}
 */
function reindexContinuousLinesOfEnumeration(linesAfterSelection, previousNumber = 0) {
  if (linesAfterSelection.length < 1) {
    return [];
  }

  const reindexedLines = [];
  for (const line of linesAfterSelection) {
    if (!isEnumerated(line)) {
      break;
    }

    reindexedLines.push(line.replace(/([0-9]+)/, (++previousNumber).toString()));
  }

  // replace all reindexed lines in the actual array of lines if necessary.
  if (reindexedLines.length > 0) {
    linesAfterSelection = reindexedLines.concat(
      linesAfterSelection.slice(reindexedLines.length),
    );
  }

  return linesAfterSelection;
}

/**
 * @param {string[]} linesOfSelection
 * @return {string[]}
 */
function toggleBulletPoints(linesOfSelection) {
  const transformedLines = [];
  const toList = !isBulletPointed(linesOfSelection[0] ?? '');
  for (const line of linesOfSelection) {
    transformedLines.push(
      (toList) ? `- ${line}` : removeBulletPointOrEnummeration(line),
    );
  }

  return transformedLines;
}

/**
 * @param {string[]} linesOfSelection
 * @param {number} nextNumber
 * @return {string[]}
 */
function toggleEnumeration(linesOfSelection, nextNumber = 1) {
  const transformedLines = [];
  const toList = !isEnumerated(linesOfSelection[0] ?? '');
  for (const line of linesOfSelection) {
    transformedLines.push(
      (toList) ? `${nextNumber++}. ${line}` : removeBulletPointOrEnummeration(line),
    );
  }

  return transformedLines;
}

/**
 * @param {string} line
 * @return {number|null}
 */
function getFirstNumber(line) {
  const numbers = line.match(/([0-9]+)/);
  if (numbers !== null) {
    return parseInt(numbers[0], 10);
  }

  return null;
}

/**
 * @param {HTMLElement} element
 * @param {string} cssClass
 * @return {void}
 */
function toggleClassOfElement(element, cssClass) {
  if (element.classList.contains(cssClass)) {
    element.classList.remove(cssClass);
  } else {
    element.classList.add(cssClass);
  }
}

/**
 * @param {KeyboardEvent} event
 * @return {boolean}
 */
function isEnterKeyPressed(event) {
  if (event instanceof KeyboardEvent) {
    return (event.code === 'Enter');
  }

  return false;
}

/**
 * @param {string} line
 * @return {string}
 */
function removeBulletPointOrEnummeration(line) {
  return line.replace(/((^(\s*[-])|(^(\s*\d+\.)))\s*)/g, '');
}

/**
 * @param {string} line
 * @return {boolean}
 */
function isEmptyListEntry(line) {
  return ((line.match(/((^(\s*-)|(^(\s*\d+\.)))\s*)$/g) ?? []).length > 0);
}

/**
 * @param {string} line
 * @return {boolean}
 */
function isBulletPointed(line) {
  return ((line.match(/^(\s*[-])/g) ?? []).length > 0);
}

/**
 * @param {string} line
 * @return {boolean}
 */
function isEnumerated(line) {
  return ((line.match(/^(\s*\d+\.)/g) ?? []).length > 0);
}

/**
 * @param {HTMLButtonElement} button
 * @return {string|null}
 */
function getActionSignalOfButton(button) {
  const actionWrapper = button.closest('span[data-signal]');
  if (!(actionWrapper instanceof HTMLSpanElement)) {
    return null;
  }

  if (!actionWrapper.hasAttribute('data-signal')) {
    return null;
  }

  return actionWrapper.dataset.signal;
}
