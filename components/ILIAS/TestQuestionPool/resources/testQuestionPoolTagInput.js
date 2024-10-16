const ilBootstrapTaggingOnLoad = (() => {
  const pub 				= {};
  const pri 				= {};
  pub.terms = [];
  pub.ids	 				= [];
  pub.selected_terms		= [];
  pub.callbackItemAdded 	= {};
  pub.callbackItemRemoved	= {};

  pub.appendId = (id) => {
    const pos = $.inArray(id, pub.ids);
    if (pos === -1) {
      pub.ids.push(id);
    }
  };

  pub.appendTerms = (id, terms) => {
    if (typeof pub.terms === 'undefined') {
      pub.terms = [];
    }
    const pos = $.inArray(id, pub.terms);
    if (pos === -1) {
      pub.terms[id] = terms;
    }
  };

  pub.initConfig = (config) => {
    pri.config = config;
  };

  pub.Init = () => {
    $.each(pub.ids, (key, element) => {
      let { terms } = pub;

      if (!Array.isArray(terms) || !terms.every((x) => typeof x === 'string')) {
        terms = pub.terms[key];
      }

      $(element).tagsinput({
        typeaheadjs: [{
          minLength: parseInt(pri.config.min_length, 10),
          highlight: pri.config.highlight,
        }, {
          limit: parseInt(pri.config.limit, 10),
          source: pri.substringMatcher(terms, key),

        }],
        freeInput: false,
        maxTags: pri.config.maxtags,
        maxChars: pri.config.maxchars,
        allowDuplicates: pri.config.allow_duplicates,
      });

      pri.preventFormSubmissionOnEnterInTypeahead();

      $(element).on('itemAdded', (elem) => {
        if (typeof pub.callbackItemAdded === 'function') {
          pub.callbackItemAdded();
          if ($(element).tagsinput()[0].options.allowDuplicates !== true) {
            if (pub.selected_terms[key] === undefined) {
              pub.selected_terms[key] = [];
            }
            const pos = $.inArray(elem.item, pub.selected_terms[key]);
            if (pos === -1) {
              pub.selected_terms[key].push(elem.item);
            }
          }
        }
      });

      $(element).on('itemRemoved', (elem) => {
        if (typeof pub.callbackItemRemoved === 'function') {
          pub.callbackItemRemoved();
          if ($(element).tagsinput()[0].options.allowDuplicates !== true) {
            const pos = $.inArray(elem.item, pub.selected_terms[key]);

            if (pos > -1) {
              pub.selected_terms[key].splice(pos, 1);
            }
          }
        }
      });
    });
  };

  pri.substringMatcher = (strings, key) => function findMatches(query, callback) {
    let matches; let substringRegex; let
      pos;
    matches = [];
    substringRegex = new RegExp(query, pri.config.case);
    $.each(strings, (i, str) => {
      if (substringRegex.test(str)) {
        pos = $.inArray(str, pub.selected_terms[key]);
        if (pos === -1) {
          matches.push(str);
        }
      }
    });
    callback(matches);
  };

  pri.preventFormSubmissionOnEnterInTypeahead = () => {
    $(document).ready(() => {
      $('.twitter-typeahead').keydown((event) => {
        if ((event.keyCode === 13)) {
          event.preventDefault();
          return false;
        }
      });
    });
  };

  return pub;
}
)();
