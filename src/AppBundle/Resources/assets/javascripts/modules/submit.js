$(function()
{
    $('form').submit(function(){
        //The disabling of the button needs to happen after submission has taken place otherwise Symfony code like:
        //$form->get('save')->isClicked() will not work.
        //This is because disabling this button happens before form submission so its value is not passed as part of the post
        //Adding a very short timeout prevents this problem and still prevents multiple form submissions
        setTimeout(function(){
            $('form').find("button[type='submit']").prop('disabled', true);
            $('form').find("input[type='submit']").prop('disabled', true);
        }, 1);
        return true;
    });
});