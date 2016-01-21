describe('Sort Code Tests', function () {

    var placeholder = $('#placeholder'),
        template = $('#template');
    
    beforeEach(function() {
        placeholder.empty('').append(template.text());

        new GOVUK.AutoSave({
            form: $('#placeholder form.expanding-transaction-table'),
            statusElement: $('#placeholder #info'),
            url: 'test'
        });
        
        // mock $.ajax
        
    });
    afterEach(function() {
        // restore $.ajax
    });
    
    describe('Save Form', function () {
        
    });
    
    describe('Tell the user we are saving');
    
    describe('Tell the user we saved on success');
    
    describe('Tell the user about form validation errors');
    
    describe('Tell the user about some other error');
    
    describe('Calls save when field blurs');
    
    describe('Calls save when form submits');
    
    describe('Calls save when page exits');
    
});
