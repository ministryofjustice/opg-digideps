/* globals jQuery */
var opg = opg || {};

(function ($, opg) {

    var Ga = function(){};

    /**
     * Send
     * @param JQuery selector
     * @param integer timeoutMs milliseconds
     */
    Ga.prototype.trackDownloadableLink = function (element, timeoutMs) {
       
        element.click(function(e) {
            e.preventDefault();
            var link = $(e.target).attr('href');
            
            // track page view with the "href" link
            ga('send', 'pageview', link);
            //console.log("tracking GA link " + link);
            
            // continue to load page
            setTimeout(function(){
                location.href = link;
            }, timeoutMs);
            
            return false;
        }); 
        
    };

    opg.Ga = Ga;

})(jQuery, opg);
