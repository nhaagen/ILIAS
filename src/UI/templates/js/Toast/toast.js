/* eslint no-use-before-define: off */
/* eslint no-unused-vars: off */
/* eslint radix: off */
/* eslint no-undef: off */
const il = il || {};
il.UI = il.UI || {};
il.UI.toast = ((UI) => {
  let vanishTime = 5000;
  let delayTime = 500;

  const setToastSettings = (element) => {
    if (element.hasAttribute('data-vanish')) {
      vanishTime = parseInt(element.dataset.vanish);
    }
    if (element.hasAttribute('data-delay')) {
      delayTime = parseInt(element.dataset.delay);
    }
  };

  const showToast = (element) => {
    setTimeout(() => { appearToast(element); }, delayTime);
  };

  const closeToast = (element, forced = false) => {
    element.querySelector('.il-toast').addEventListener('transitionend', () => {
      if (forced && element.dataset.vanishurl !== '') {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', element.dataset.vanishurl);
        xhr.send();
      }
      element.remove();
      element.dispatchEvent(new Event('removeToast'));
    });
    element.querySelector('.il-toast').classList.remove('active');
  };

  let appearToast = (element) => {
    element.querySelector('.il-toast').classList.add('active');
    element.querySelector('.il-toast .close').addEventListener('click', () => { closeToast(element, true); });
    if (element.hasAttribute('data-vanish')) {
      setTimeout(() => { closeToast(element); }, vanishTime);
    }
  };

  return {
    showToast,
    closeToast,
    appearToast,
    setToastSettings,
  };
})(il.UI);
