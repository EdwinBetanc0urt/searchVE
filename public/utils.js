
/**
 * Checks if value is empty. Deep-checks arrays and objects
 * Note: isEmpty([]) == true, isEmpty({}) == true,
 * isEmpty([{0: false}, "", 0]) == true, isEmpty({0: 1}) == false
 * @param {array|boolean|map|number|object|set|string} value
 * @returns {boolean}
 * @url https://gist.github.com/EdwinBetanc0urt/3fc02172ada073ded4b52e46543553ce
 */
export const isEmptyValue = function(value) {
  let isEmpty = false;
  const typeOfValue = Object.prototype
    .toString
    .call(value)
    .match(/^\[object\s(.*)\]$/)[1];

  switch (typeOfValue) {
    case 'Undefined':
    case 'Error':
    case 'Null':
      isEmpty = true;
      break;
    case 'Boolean':
    case 'Date':
    case 'Function': // or class
    case 'Promise':
    case 'RegExp':
      isEmpty = false;
      break;
    case 'String':
      isEmpty = Boolean(!value.trim().length);
      break;
    case 'Math':
    case 'Number':
      if (Number.isNaN(value)) {
        isEmpty = true;
        break;
      }
      isEmpty = false;
      break;
    case 'JSON':
      if (value.trim().length) {
        isEmpty = Boolean(value.trim() === '{}');
        break;
      }
      isEmpty = true;
      break;
    case 'Object':
      isEmpty = Boolean(!Object.keys(value).length);
      break;
    case 'Arguments':
    case 'Array':
      isEmpty = Boolean(!value.length);
      break;
    case 'Map':
    case 'Set':
      isEmpty = Boolean(!value.size);
      break;
  }

  return isEmpty;
};
