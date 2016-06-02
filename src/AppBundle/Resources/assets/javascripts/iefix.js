/*jshint browser: true */
// http://stackoverflow.com/questions/6918943/substr-with-negative-value-not-working-in-ie
// format currency broken because of this
// only run when the substr() function is broken
if ('ab'.substr(-1) != 'b') {
    /**
     *  Get the substring of a string
     *  @param  {integer}  start   where to start the substring
     *  @param  {integer}  length  how many characters to return
     *  @return {string}
     */
    String.prototype.substr = function(substr) {
        return function(start, length) {
            // call the original method
            return substr.call(this,
                // did we get a negative start, calculate how much it is from the beginning of the string
                // adjust the start parameter for negative value
                start < 0 ? this.length + start : start,
                length);
        };
    }(String.prototype.substr);
}

alert("5000".substr(-3));