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

        it('should call save when a user submits a form', function () {
            $('#placeholder input').eq(0).trigger('keyup');
            $('#placeholder form').eq(0).trigger('submit');
            expect(saveStub.callCount).to.equal(1); 
        });
        
        it('should not call save then whe user moves off a field but did not interact with it', function () {
            $('#placeholder input').eq(0).trigger('blur');
            expect(saveStub.callCount).to.equal(0);
        });
        it('should not call save on form submit if nothing changed', function () {
            $('#placeholder form').eq(0).trigger('submit');
            expect(saveStub.callCount).to.equal(0);
        });
        
        it('should not call save on form submit if already saved through blur', function () {
            $('#placeholder input').eq(0).trigger('keyup').trigger('blur');
            $('#placeholder form').eq(0).trigger('submit');
            expect(saveStub.callCount).to.equal(1);
        });

    });
    
    describe('Tell the user we are saving', function () {
        it('should change the info indicator to say saving when starts saving', function () {
                
        });
        it('should say saved after data has been saved', function () {
            
        });
        it('should say not saved if it received any kind of error', function () {
            
        });
        it('should remove the saved indicator when a user changes something', function () {

        });
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
    
    describe('When I pretend to submit a form, goto the defined location', function () {
        
    });
});
