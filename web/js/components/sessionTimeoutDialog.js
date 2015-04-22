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
        this.refreshUrl = options.refreshUrl;
        this.redirectAfterMs = 3000;
        this.startCountdown();
        
        return this;
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
    // ok button: restart countdowns
    hidePopupAndRestartCountdown: function() {
      //
      this.element.css('visibility', 'hidden');
      this.refreshPageHiddenMode();
      // restart countdown
      clearTimeout(this.countDownPopup);
      clearTimeout(this.countDownLogout);
      this.startCountdown();
    },
    // close: hide popup but after the timeout it still logs you out
    closePopup: function() {
      this.element.css('visibility', 'hidden');
//      clearTimeout(this.countDownPopup);
    },
    refreshPageHiddenMode: function() {
      $.ajax({
        type: "POST",
        url: this.refreshUrl,
        data: {},
        success: function(data) {console.log(data);},
        error: function(data) {console.log(data);}
      });
    },
    getElement: function () {
      return this.element;
    }
};