describe('Sort Code Tests', function () {

    var placeholder = $('#placeholder'),
        template = $('#template'),
        autosave,
        ajaxSpy;
    
    beforeEach(function() {
        placeholder.empty('').append(template.html());

        autosave = new GOVUK.AutoSave({
            form: $('#placeholder form.expanding-transaction-table'),
            statusElement: $('#placeholder #info'),
            url: 'test'
        });
        
        ajaxSpy = sinon.stub(jQuery, 'ajax', function(options) {
            options.done({status:'success'});
        });
        
    });
    afterEach(function() {
        ajaxSpy.restore();
    });
    
    describe('Trigger save form', function () {
        
        var saveStub;
        
        beforeEach(function () {
            saveStub = sinon.spy(autosave, 'save');
        });
        
        afterEach(function () {
            saveStub.restore();
        });
        
        it('should call save then whe user moves off a field after changing something manually', function () {
            $('#placeholder input').eq(0).trigger('keyup').trigger('blur');
            expect(saveStub.callCount).to.equal(1);
        });
        it('should call save then whe user moves off a field after changing something through paste', function () {
            $('#placeholder input').eq(0).trigger('paste').trigger('blur');
            expect(saveStub.callCount).to.equal(1);
        });
        it('should not call save then whe user moves off a field but not interact with it', function () {
            $('#placeholder input').eq(0).trigger('blur');
            expect(saveStub.callCount).to.equal(1);
        });
        it('should call save when a user submits a form', function () {
            $('#placeholder form').eq(0).trigger('submit');
            expect(saveStub.callCount).to.equal(1); 
        });
        it('should call save when a user moves off the page', function () {
            // When I leave a page through submit, how is this captured?
            
            // how about, when I submit a form, set a flag to say don't save (because already saved)
            // or on paste and key up set status to changed, then after saving set status to saved
        });

        it('should not call save on form submit if already saved through blur', function () {

        });

        it('should not call save on page leave and form submitted', function () {
            
        });
        it('should not call save on page leave if saved through blur', function () {
            
        });

    });
    
    describe('Tell the user we are saving', function () {
        
    });
    
    describe('Tell the user we saved on success', function () {
        
    });
    
    describe('Tell the user about form validation errors', function () {
        
    });
    
    describe('Tell the user about some other error', function () {
        
    });
    
    describe('Calls save when field blurs', function () {
        
    });
    
    describe('Calls save when form submits', function () {
        
    });
    
    describe('Calls save when page exits', function () {
        
    });
    
    describe('When I pretend to submit a form, goto the defined location');
});
