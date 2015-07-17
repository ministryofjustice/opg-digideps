function mojDateString(localDate) {
    if (!localDate) {
        return;
    }
    var padder = function (size) {
            return ("0000" + this.toString()).substr(size * -1, size)
        },
        datestring = '',
        offSet = localDate.getTimezoneOffset() * 60000,
        objDate = new Date(localDate + offSet),
        days = padder.call(objDate.getUTCDate(), 2),
        month = padder.call(objDate.getUTCMonth() + 1, 2),
        year = objDate.getUTCFullYear(),
        hours = padder.call(objDate.getUTCHours(), 2),
        minutes = padder.call(objDate.getUTCMinutes(), 2);

    return days + "/" + month + "/" + year + "  " + hours + ":" + minutes;

}

function scrollIntoView(eleID) {
   var element = $('#' + eleID);
   if (element.length === 1) {
        $('html, body').animate({scrollTop: element.offset().top -40 }, 'fast');
   }
}

var dateNow = new Date();

//debugger;
var output = mojDateString(dateNow);

if ($('.registration-client-add')) {
    new GOVUK.SelectionButtons(":checkbox");
}

if ($('.js-report-decisions-input')) {
    new GOVUK.SelectionButtons(":radio");
}

$(window).load(function(){
    scrollIntoView('error-summary');   
});