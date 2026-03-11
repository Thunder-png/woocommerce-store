;(function () {
  if (typeof window === 'undefined') {
    return;
  }

  var cryptoObj = window.crypto || window.msCrypto || {};

  if (typeof cryptoObj.randomUUID !== 'function') {
    function fallbackRandomUUID() {
      var template = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx';

      if (cryptoObj.getRandomValues && typeof Uint8Array !== 'undefined') {
        var bytes = new Uint8Array(16);
        cryptoObj.getRandomValues(bytes);
        var i = 0;

        return template.replace(/[xy]/g, function (c) {
          var r = bytes[i++] % 16;
          var v = c === 'x' ? r : (r & 0x3) | 0x8;
          return v.toString(16);
        });
      }

      return template.replace(/[xy]/g, function (c) {
        var r = (Math.random() * 16) | 0;
        var v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
      });
    }

    cryptoObj.randomUUID = fallbackRandomUUID;
  }

  window.crypto = cryptoObj;

  // Tracking script guard: define no-op pixels if eksik.
  if (typeof window.rdt !== 'function') {
    window.rdt = function () {};
  }

  if (typeof window.snaptr !== 'function') {
    window.snaptr = function () {};
  }
})();

