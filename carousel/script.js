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

const swipeDetect = function() {
  const host = $('html');

  if (host.data('swipeDetect') !== undefined)
    return;

  host.data('swipeDetect', 1);
  
  document.addEventListener('touchstart', handleTouchStart, false);
  document.addEventListener('touchmove', handleTouchMove, false);
  
  let xDown = null;
  let yDown = null;

  function getTouches(evt) {
    return evt.touches ||             // browser API
           evt.originalEvent.touches; // jQuery
  }                                                     
  
  function handleTouchStart(evt) {
    const firstTouch = getTouches(evt)[0];
    xDown = firstTouch.clientX;
    yDown = firstTouch.clientY;
  }
  
  function handleTouchMove(evt) {
      if (!xDown || !yDown ) return;
  
      const xUp = evt.touches[0].clientX;
      const yUp = evt.touches[0].clientY;  
      const xDiff = xDown - xUp;
      const yDiff = yDown - yUp;
    
      if (Math.abs(xDiff) > Math.abs(yDiff)) {        
          if (xDiff > 0) {
            host.trigger('swipeleft');
          } else {
            host.trigger('swiperight');            
          }                       
      } else {        
          if (yDiff > 0) {
            host.trigger('swipeup');
          } else { 
            host.trigger('swipedown');            
          }
      }

      xDown = null;
      yDown = null;
  }    
};