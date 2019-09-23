// https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_objects/Function/bind
if (!Function.prototype.bind) {
  /* eslint-disable-next-line no-extend-native */
  Function.prototype.bind = function (oThis) {
    if (typeof this !== 'function') {
      // closest thing possible to the ECMAScript 5
      // internal IsCallable function
      throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable')
    }

    var aArgs = Array.prototype.slice.call(arguments, 1)
    var fToBind = this
    var fNOP = function () {}
    var fBound = function () {
      return fToBind.apply(this instanceof fNOP ? this : oThis, aArgs.concat(Array.prototype.slice.call(arguments)))
    }

    if (this.prototype) {
      // native functions don't have a prototype
      fNOP.prototype = this.prototype
    }
    fBound.prototype = new fNOP() // eslint-disable-line new-cap

    return fBound
  }
}
