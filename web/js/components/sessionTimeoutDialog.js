// SESSION TIMEOUT POPUP LOGIC    
var sessionTimeoutPopup = {
    // init
    init: function (element, sessionExpiresMs, sessionPopupShowAfterMs, refreshUrl) {
        this.element = element;
        this.sessionExpiresMs = sessionExpiresMs;
        this.sessionPopupShowAfterMs = sessionPopupShowAfterMs;
        this.refreshUrl = refreshUrl;
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
        this.sessionExpiresMs + 5000
      );
    },
    // ok button: restart countdowns
    ok: function() {
      //
      this.element.css('visibility', 'hidden');
      this.refreshPageHiddenMode();
      // restart countdown
      clearTimeout(this.countDownPopup);
      clearTimeout(this.countDownLogout);
      this.startCountdown();
    },
    // close: hide popup but after the timeout it still logs you out
    close: function() {
      this.element.css('visibility', 'hidden');
      clearTimeout(this.countDownPopup);
    },
    refreshPageHiddenMode: function() {
      $.ajax({
        type: "POST",
        url: this.refreshUrl,
        data: {},
        success: function(data) {console.log(data);},
        error: function(data) {console.log(data);}
      });
    }
};