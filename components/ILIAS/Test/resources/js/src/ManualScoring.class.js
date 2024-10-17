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

export default class ManualScoring {
  /**
   * @type {DOMParser}
   */
  #DOMParser;

  /**
   * @param {DOMParser} DOMParser
   * @param {string} componentId
   * @throws {Error} if DOM element is missing
   */
  constructor(DOMParser) {
    this.#DOMParser = DOMParser;
  }

  asynchForm(container) {
    // const form = container.querySelector('form');
    const form = container;
    form.addEventListener(
      'submit',
      (e) => {
        e.preventDefault();
        this.formLoader(form);
        return false;
      },
    );
  }

  async formLoader(form) {
    const url = form.action;
    const par = {
      method: form.method,
      body: new FormData(form),
    };

    await fetch(url, par)
      .then(
        (resp) => resp.text(),
      )
      .then(
        (html) => {
          const parser = new this.#DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const nuForm = doc.forms.item(0);
          form.replaceWith(nuForm);
          const script = document.createElement('script');
          script.text = doc.scripts.item(0).text;
          nuForm.appendChild(script);
          const msg = doc.querySelector('div.alert[role="status"]');
          if (msg) {
            nuForm.querySelector('.c-form__header').after(msg);
          }
        },
      );
  }
}
