// SESSION TIMEOUT POPUP LOGIC  
/**
 * @param element
 * @param sessionExpiresMs
 * @param sessionPopupShowAfterMs
 * @param refreshUrl
 */
var SessionTimeoutDialog = function (options) {
  var that = this;
  this.element = options.element;
  this.sessionExpiresMs = options.sessionExpiresMs;
  this.sessionPopupShowAfterMs = options.sessionPopupShowAfterMs;
  this.keepSessionAliveUrl = options.keepSessionAliveUrl;
  this.redirectAfterMs = 3000;
  // attach event
  this.element.find('[data-role="keep-session-alive"]').click(function (e) {
    e.preventDefault();
    that.hidePopupAndRestartCountdown();
  });
  this.startCountdown = function () {
    this.countDownPopup = window.setInterval(
        function () {
          that.element.css('visibility', 'visible');
        },
        this.sessionPopupShowAfterMs
        );
    this.countDownLogout = window.setInterval(
      function () {
        window.location.reload();
      },
      this.sessionExpiresMs + this.redirectAfterMs
      );
  };
  this.hidePopupAndRestartCountdown = function () {
    this.element.css('visibility', 'hidden');
    this.keepSessionAlive();
    // restart countdown
    clearTimeout(this.countDownPopup);
    clearTimeout(this.countDownLogout);
    this.startCountdown();
  };
  this.keepSessionAlive = function () {
    $.post(this.keepSessionAliveUrl);
  };
};