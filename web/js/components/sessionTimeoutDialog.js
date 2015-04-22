// SESSION TIMEOUT POPUP LOGIC    
var SessionTimeoutDialog = {
    /**
     * @param element
     * @param sessionExpiresMs
     * @param sessionPopupShowAfterMs
     * @param refreshUrl
     */
    init: function (options) {
        this.element = options.element;
        this.sessionExpiresMs = options.sessionExpiresMs;
        this.sessionPopupShowAfterMs = options.sessionPopupShowAfterMs;
        this.keepSessionAliveUrl = options.keepSessionAliveUrl;
        this.redirectAfterMs = 3000;
        this.startCountdown();
        var that = this;
        
        
        // attach event
        this.element.find('[data-role="keep-session-alive"]').click(function(e) {
          e.preventDefault();
          that.hidePopupAndRestartCountdown();
        });
    },
    startCountdown: function() {
      var that = this;
      this.countDownPopup = window.setInterval(
        function() {
          that.element.css('visibility', 'visible');
        },
        this.sessionPopupShowAfterMs
      );
      this.countDownLogout = window.setInterval(
        function() {
          window.location.reload();
        },
        this.sessionExpiresMs + this.redirectAfterMs
      );
    },
    hidePopupAndRestartCountdown: function() {
      this.element.css('visibility', 'hidden');
      this.keepSessionAlive();
      // restart countdown
      clearTimeout(this.countDownPopup);
      clearTimeout(this.countDownLogout);
      this.startCountdown();
    },
    keepSessionAlive: function() {
      $.post(this.keepSessionAliveUrl);
    },
    getElement: function () {
      return this.element;
    }
};