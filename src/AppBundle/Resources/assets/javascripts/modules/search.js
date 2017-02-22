// Add .focus class if there's text already there (on load)
var $search = $('.js-search-focus');
if ($search.val() !== '') {
    $search.addClass('focus');
}
// Add .focus class when input gets focus
$search.on('focus', function(){
    if (!$(this).hasClass('focus')){
        $(this).addClass('focus');
    }
});
// Remove .focus class if input is blurred
$search.on('blur', function(){
    if ($(this).val() === '') {
        $(this).removeClass('focus');
    }
});