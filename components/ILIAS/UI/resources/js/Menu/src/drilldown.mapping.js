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

export default class DrilldownMapping {
  /**
   * @type {object}
   */
  #classes = {
    DRILLDOWN: 'c-drilldown',
    MENU: 'c-drilldown__menu',
    MENU_FILTERED: 'c-drilldown--filtered',

    MENU_LEVEL: 'c-drilldown__menulevel',

    HEADER_ELEMENT: 'c-drilldown__menulevel--trigger',
    MENU_BRANCH: 'c-drilldown__branch',
    MENU_LEAF: 'c-drilldown__leaf',
    FILTER: 'c-drilldown__filter',

    ACTIVE: 'c-drilldown__menulevel--engaged',

    ACTIVE_ITEM: 'c-drilldown__menuitem--engaged', // on li
    ACTIVE_PARENT: 'c-drilldown__menulevel--engagedparent', // on ul

    FILTERED: 'c-drilldown__menuitem--filtered',
    WITH_BACKLINK_ONE_COL: 'c-drilldown__header--showbacknav',
    WITH_BACKLINK_TWO_COL: 'c-drilldown__header--showbacknavtwocol',
    HEADER_TAG: 'header',
    LIST_TAG: 'ul',
    LIST_ELEMENT_TAG: 'li',
    ID_ATTRIBUTE: 'data-ddindex',
    MENU_LEVEL_LABEL: 'c-drilldown__menulevel--label',
    SEARCHABLE_TEXT: 'c-drilldown__menulevel--searchabletext',

  };

  /**
   * @type {object}
   */
  #elements = {
    dd: null,
    header: null,
    levels: [],
  };

  /**
   * @type {Document}
   */
  #document;

  /**
   * @type {ResizeObserver}
   */
  #resizeObserver;

  /**
   * @param {Document} document
   * @param {ResizeObserver} resizeObserver
   * @param {string} drilldownId
   */
  constructor(document, resizeObserver, drilldownId) {
    this.#document = document;
    this.#resizeObserver = resizeObserver;
    this.#elements.dd = document.getElementById(drilldownId);
    [this.#elements.header] = this.#elements.dd.getElementsByTagName(this.#classes.HEADER_TAG);
  }

  /**
   * @returns {HTMLUListElement}
   */
  #getMenuContainer() {
    return this.#elements.dd.querySelector(`.${this.#classes.MENU}`);
  }

  /**
   * @param {function} filterHandler
   * @return {void}
   */
  maybeAddFilterHandler(filterHandler) {
    this.#elements.header.querySelector(`.${this.#classes.FILTER} > input`)?.addEventListener(
      'keyup',
      filterHandler,
    );
  }

  /**
   * Parse newly added drilldown levels. This also works in async context.
   * @param {function} levelRegistry
   * @param {function} leafBuilder
   * @param {function} clickHandler
   * @return {void}
   */
  // parseLevel(levelRegistry, leafBuilder, clickHandler, levelFactory) {
  parseLevel(clickHandler, levelFactory) {
    const levels = this.#getMenuContainer().querySelectorAll(`.${this.#classes.MENU_LEVEL}`);
    levels.forEach(
      (level, index) => {
        const levelId = index + 1;
        // const levelId = index;
        level.setAttribute(this.#classes.ID_ATTRIBUTE, levelId);

        // no label if divider (<li><hr></li>)
        let label = level.querySelector(`.${this.#classes.MENU_LEVEL_LABEL} summary`)?.innerText
          ?? level.querySelector(`.${this.#classes.MENU_LEVEL_LABEL}`)?.innerText
          ?? level.textContent;

        const details = level.querySelector(`.${this.#classes.MENU_LEVEL_LABEL} details`)?.innerText;

        let searchableText = (details ?? label) ?? '';

        label = label.replace(/\s+/g, ' ').trim();
        searchableText = searchableText.replace(/\s+/g, ' ').trim();

        const parent = level.parentNode.closest(`.${this.#classes.MENU_LEVEL}`);
        const parentId = parseInt(parent?.getAttribute(this.#classes.ID_ATTRIBUTE), 10) || 0;

        // no handler for leaves!!!
        level.querySelector(`.${this.#classes.HEADER_ELEMENT}`)
          ?.addEventListener('click', () => clickHandler(levelId));
        // this.engageLevel(levelId, entryId);

        levelFactory(levelId, parentId, label, searchableText);
        this.#elements.levels[levelId] = level;
      },
    );
    this.#elements.levels[0] = null;

    /*
    const sublists = this.#getMenuContainer().querySelectorAll(this.#classes.LIST_TAG);
    walkArray(sublists, (sublist) => {
      const levelId = sublist.getAttribute(this.#classes.ID_ATTRIBUTE);
      const level = levelRegistry( // from model
        this.#getLabelForList(sublist),
        this.#getParentIdOfList(sublist),
        this.#getLeavesOfList(sublist, leafBuilder),
        levelId,
      );
      if (levelId === null) {
        this.#addLevelId(sublist, level.id);
        // console.log(sublist)
        const nuLevelId = sublist.closest('.c-drilldown__menulevel')?.getAttribute('data-ddindex') ?? 0;
        this.registerHandler(sublist, clickHandler, level.id, parseInt(nuLevelId, 10));
        // this.#elements.levels[level.id] = sublist;
      }
    });
*/
  }

  /**
   * @param {HTMLUListElement} list
   * @param {string} levelId
   * @returns {void}
  #addLevelId(list, levelId) {
    const listRef = list;
    listRef.setAttribute(this.#classes.ID_ATTRIBUTE, levelId);
  }
   */

  /**
   * @param {HTMLUListElement} list
   * @return {HTMLHeadElement}
   */
  #getLabelForList(list) {
    const headerElement = list.parentElement.querySelector(`:scope > .${this.#classes.HEADER_ELEMENT}`);
    if (headerElement === null) {
      return null;
    }
    let header = null;
    header = this.#document.createElement('h2');
    header.textContent = headerElement.innerText;
    return header;
  }

  /**
   * @param {HTMLUListElement} list
   * @returns {string}
  #getParentIdOfList(list) {
    return list.parentElement.parentElement.getAttribute(this.#classes.ID_ATTRIBUTE);
  }
   */

  /**
   * @param {HTMLUListElement} list
   * @return {object}
  #getLeavesOfList(list, leafBuilder) {
    const leafElements = list.querySelectorAll(`:scope >.${this.#classes.MENU_LEAF}`);
    const leaves = [];
    walkArray(leafElements, (leafElement, index) => {
      leaves.push(
        leafBuilder(
          index,
          leafElement.firstElementChild.innerText,
        ),
      );
    });
    return leaves;
  }
   */

  /**
   * @param {HTMLUListElement} list
   * @param {function} handler
   * @param {string} elementId
   * @returns {void}
  registerHandler(list, handler, elementId, levelId) {
    const headerElement = list.parentElement.querySelector(`:scope > .${this.#classes.HEADER_ELEMENT}`);
    if (headerElement === null) {
      return;
    }
   */

  /*
    console.group('register')
    console.log(elementId)
    console.log(levelId)
    console.groupEnd()
    headerElement.addEventListener('click', () => {
      handler(elementId, levelId);
    });
  }
    */

  /**
   * @param {string} level
   * @return {void}
   */
  setEngaged(level) {
    this.#elements.dd.querySelector(`.${this.#classes.ACTIVE}`)
      ?.classList.remove(`${this.#classes.ACTIVE}`);
    this.#elements.dd.querySelector(`.${this.#classes.ACTIVE_ITEM}`)
      ?.classList.remove(`${this.#classes.ACTIVE_ITEM}`);
    this.#elements.dd.querySelector(`.${this.#classes.ACTIVE_PARENT}`)
      ?.classList.remove(`${this.#classes.ACTIVE_PARENT}`);

    const activeLevel = this.#elements.levels[level]; // li
    console.log(`setEngaged: ${level}`);
    console.log(activeLevel);

    if (activeLevel === null) {
      console.log('setEngaged ROOT');
      this.#elements.dd.querySelector(`.${this.#classes.MENU} > ul`)
        .classList.add(this.#classes.ACTIVE);

      this.#elements.dd.querySelector(`.${this.#classes.MENU} > ul`)
        .classList.add(this.#classes.ACTIVE_PARENT);

      this.correctRightColumnPositionAndHeight(0);
      return;
    }

    activeLevel.classList.add(this.#classes.ACTIVE_ITEM);
    // ul
    activeLevel.parentElement.classList.add(this.#classes.ACTIVE_PARENT);
    activeLevel.querySelector('ul')?.classList.add(this.#classes.ACTIVE);

    // const lower = this.#elements.levels[level].querySelector(':scope > li')?.firstElementChild;
    // lower?.focus();
    this.correctRightColumnPositionAndHeight(level);
  }

  /**
   * @param {object[]} filteredElements
   * @return {void}
   */
  setFiltered(filteredItems) {
    const levels = this.#elements.dd.querySelectorAll(`${this.#classes.LIST_TAG}`);
    const leaves = this.#elements.dd.querySelectorAll(`.${this.#classes.MENU_LEAF}`);
    const filteredItemsIds = filteredItems.map((v) => v.id);
    const topLevelItems = this.#elements.dd.querySelectorAll(
      `.${this.#classes.MENU} > ul > .${this.#classes.MENU_BRANCH}`,
    );

    this.#elements.levels.forEach(
      (e) => {
        const eRef = e;
        eRef.style.removeProperty('top');
        eRef.style.removeProperty('height');
      },
    );

    leaves.forEach(
      (element) => {
        const elemRef = element;
        elemRef.classList.remove(this.#classes.FILTERED);
      },
    );

    if (filteredItems.length === 0) {
      this.#elements.dd.classList.remove(this.#classes.MENU_FILTERED);
      topLevelItems.forEach(
        (element) => {
          const elemRef = element;
          elemRef.firstElementChild.disabled = false;
          elemRef.classList.remove(this.#classes.FILTERED);
        },
      );
      this.correctRightColumnPositionAndHeight(0);
      return;
    }

    this.setEngaged(0);
    this.#elements.dd.classList.add(this.#classes.MENU_FILTERED);
    topLevelItems.forEach(
      (element) => {
        const elemRef = element;
        elemRef.firstElementChild.disabled = true;
        elemRef.classList.remove(this.#classes.FILTERED);
      },
    );

    filteredItemsIds.forEach(
      (id, index) => {
        const [element] = [...levels].filter(
          (level) => level.getAttribute(this.#classes.ID_ATTRIBUTE) === id,
        );
        const elementChildren = element.querySelectorAll(`:scope >.${this.#classes.MENU_LEAF}`);
        filteredItems[index].leaves.forEach(
          (leaf) => elementChildren[leaf.index].classList.add(this.#classes.FILTERED),
        );
      },
    );

    topLevelItems.forEach(
      (element) => {
        const filteredElements = element.querySelectorAll(
          `.${this.#classes.MENU_LEAF}:not(.${this.#classes.FILTERED})`,
        );
        if (filteredElements.length === 0) {
          const elemRef = element;
          elemRef.classList.add(this.#classes.FILTERED);
        }
      },
    );
  }

  setHeader(levelId, parentId) {
    const headerWrapper = this.#document.createElement('div');
    if (levelId === 0 || parentId === 0) {
      this.#elements.header.firstElementChild.replaceWith(headerWrapper);
      return;
    }

    const levelHeader = this.#elements.levels[parentId].querySelector(`.${this.#classes.HEADER_ELEMENT}`);
    const headerElement = this.#document.createElement('h2');

    headerElement.innerText = levelHeader.textContent.replace(/\s+/g, ' ').trim();

    headerWrapper.appendChild(headerElement);
    this.#elements.header.firstElementChild.replaceWith(headerWrapper);
  }

  /**
   * @param {integer} level
   * @return {void}
   */
  setHeaderBacknav(levelId, parentId) {
    this.#elements.header.classList.remove(this.#classes.WITH_BACKLINK_TWO_COL);
    this.#elements.header.classList.remove(this.#classes.WITH_BACKLINK_ONE_COL);
    if (levelId === 0) {
      return;
    }
    if (parentId !== 0) {
      this.#elements.header.classList.add(this.#classes.WITH_BACKLINK_TWO_COL);
    }
    this.#elements.header.classList.add(this.#classes.WITH_BACKLINK_ONE_COL);
  }

  /**
   * @param {integer} levelId
   * @return {void
   */
  correctRightColumnPositionAndHeight(levelId) {
    const menu = this.#elements.dd.querySelector(`.${this.#classes.MENU}`);
    const height = this.#elements.dd.querySelector(`.${this.#classes.MENU}`).offsetHeight;

    if (height === 0) {
      const triggerResize = new this.#resizeObserver((element) => {
        if (element[0].target.offsetHeight > 0) {
          this.correctRightColumnPositionAndHeight(levelId);
          triggerResize.unobserve(menu);
        }
      });
      triggerResize.observe(menu);
      return;
    }

    menu.querySelectorAll('ul').forEach(
      (list) => {
        list.style.removeProperty('top');
        list.style.removeProperty('height');
      },
    );

    const sublist = (levelId === 0) ? 1 : levelId;
    const elem = this.#elements.levels[sublist].querySelector('ul');

    if (elem === null || elem.offsetHeight === 0) {
      return;
    }
    elem.style.top = `-${elem.offsetTop}px`;
    elem.style.height = `${height}px`;
  }
}
