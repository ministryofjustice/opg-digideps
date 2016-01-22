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
            options.done({'success': true});
        });
        
    });
    afterEach(function() {
        ajaxSpy.restore();
        placeholder.text('');
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
        
        var setInfoSpy;
        
        beforeEach(function() {
            setInfoSpy = sinon.spy(autosave, 'displayStatus');
        });
        afterEach(function () {
            setInfoSpy.restore();
        });
        
        it('should change the info indicator to say saving when starts saving', function () {
            autosave.save();
            expect(setInfoSpy.calledWith({label:'Saving...', state:'saving'})).to.be.ok;
        });
        it('should say saved after data has been saved', function () {
            autosave.save();
            expect(setInfoSpy.calledWith({label:'Saved', state:'saved'})).to.be.ok;
            expect($('#placeholder #info').text()).to.equal('Saved');
        });
        it('should say not saved if it received any kind of error', function () {
            ajaxSpy.restore();
            
            ajaxSpy = sinon.stub(jQuery, 'ajax', function(options) {
                options.fail({
                    'success':false,
                    'errors': {
                        errorCode: 1002
                    }    
                });
            });
            
            autosave.save();
            expect(setInfoSpy.calledWith({label:'Not saved', state:'notsaved'})).to.be.ok;
            expect($('#placeholder #info').text()).to.equal('Not saved');
        });
        it('should remove the saved indicator when a user changes something', function () {
            autosave.save();
            expect($('#placeholder #info').text()).to.equal('Saved');
            $('#placeholder input').eq(0).trigger('keyup');
            expect($('#placeholder #info').text()).to.equal('');
        });
    });
    describe('Tell the user about form validation errors', function () {
        
        var firstField, 
            formgroup;
        
        beforeEach(function () {
            ajaxSpy.restore();

            ajaxSpy = sinon.stub(jQuery, 'ajax', function(options) {
                options.fail({
                    'success':false,
                    'errors': {
                        'errorCode': 1001,
                        'errorDescription': 'Form validation error',
                        'fields': {
                            'transactions_transactionsIn_0_amount':'ERROR MESSAGE'
                        }
                    }
                });
            });

            autosave.save();
            
            firstField = $('#placeholder #transactions_transactionsIn_0_amount');
            formgroup = firstField.parent();
            
        });
        
        it('should show an error message below a field label if there is a validation error', function () {
            expect(formgroup.find('label').next()).to.have.class('error-message');
        });
        it('should indicate the form group has an error', function () {
            expect(formgroup).to.have.class('error'); 
        });

    });
    describe('Send data to server', function () {
        it('should use a http PUT to send data to the server', function () {
            autosave.save();
            expect(ajaxSpy.firstCall.args[0].type).to.equal('PUT');
        });
        it('should send the contents of the form to the server', function () {
            var data = $('#placeholder form').serialize();
            autosave.save();
            expect(ajaxSpy.firstCall.args[0].data).to.equal(data);
        });
        it('should send the data to the url provided', function () {
            autosave.save();
            expect(ajaxSpy.firstCall.args[0].url).to.equal('test');
        });
    });
});
