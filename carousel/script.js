/* Carousel scripts */

function parseUriParams() {
  const list = {};
  const parts = document.location.href.split('?');
  
  if (parts.length > 1) {      
    const params = parts.pop().split('&');  
    params.forEach(pair => {
      const keyvalue = pair.split('=');
      const k = keyvalue.shift();
      const v = keyvalue.shift();
      list[k] = v;
    });
  }

  return list;
}

const storageWrite = function(key, data) {      
  if (window.localStorage === undefined) return false;
  try {
    if (!key) throw new Error('key is required');
    if (!data) {
      window.localStorage.removeItem(key);
    } else {      
      const str = JSON.stringify(data);
      window.localStorage.setItem(key, str);
      console.debug('storageWrite', key + ' length', str.length);
    }
    return true;
  } catch(err) {
    conosle.error('storageWrite', err);
    return false;
  }
};

const storageRead = function(key) {      
  if (window.localStorage === undefined) return false;
  try {
    if (!key) throw new Error('key is required');
    const data = window.localStorage.getItem(key);
    console.debug('storageRead', key + ' length', data ? data.length : 'null');
    return data ? JSON.parse(data) : false;
  } catch(err) {
    conosle.error('storageRead', err);
    return false;
  }
};

const shuffle = function (array) {
  let c = array.length,  i;
  while (c != 0) {
    i = Math.floor(Math.random() * c--);
    [array[c], array[i]] = [array[i], array[c]];
  }
  return array;
};