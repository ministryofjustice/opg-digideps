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
      this.interval = window.setInterval(
        function() {
          that.element.css('visibility', 'visible');
        },
        this.sessionPopupShowAfterMs
      );
    },
    ok: function() {
      //
      this.element.css('visibility', 'hidden');
      this.refreshPageHiddenMode();
      // restart countdown
      clearTimeout(this.interval);
      this.startCountdown();
    },
    close: function() {
      this.element.css('visibility', 'hidden');
      clearTimeout(this.interval);
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