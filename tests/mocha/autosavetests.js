describe('Sort Code Tests', function () {

    var placeholder = $('#placeholder'),
        template = $('#template'),
        autosave,
        ajaxSpy,
        form,
        firstInput;
    
    beforeEach(function() {
        placeholder.empty('').append(template.html());
        firstInput = $('#placeholder input#transactions_transactionsIn_0_amount').eq(0);
        form = $('#placeholder form').eq(0);
        
        autosave = new GOVUK.AutoSave({
            form: $('#placeholder form.expanding-transaction-table'),
            statusElement: $('#placeholder #info'),
            url: 'test'
        });
        
        ajaxSpy = sinon.stub(jQuery, 'ajax', function(options) {
            options.success({'success': true});
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
            validKey(firstInput);
            firstInput.trigger('blur');
            expect(saveStub.callCount).to.equal(1);
        });
        it('should call save then the user moves off a field after changing something through paste', function () {
            firstInput.trigger('paste').trigger('blur');
            expect(saveStub.callCount).to.equal(1);
        });
        it('should call save when a user submits a form', function () {
            validKey(firstInput);
            form.trigger('submit');
            expect(saveStub.callCount).to.equal(1); 
        });
        it('should not call save then whe user moves off a field but did not interact with it', function () {
            firstInput.trigger('blur');
            expect(saveStub.callCount).to.equal(0);
        });
        it('should not call save on form submit if nothing changed', function () {
            form.trigger('submit');
            expect(saveStub.callCount).to.equal(0);
        });
        it('should not call save on form submit if already saved through blur', function () {
            validKey(firstInput);
            firstInput.trigger('blur');
            form.trigger('submit');
            expect(saveStub.callCount).to.equal(1);
        });
        it('should only call save once when you enter a number then press tab', function () {
            validKey(firstInput);
            firstInput.trigger('blur');
            tabKey(firstInput);
            expect(saveStub.callCount).to.equal(1);
        });
    });
    describe('Tell the user what is going on', function () {
        
        var setInfoSpy,
            info;
        
        beforeEach(function() {
            setInfoSpy = sinon.spy(autosave, 'displayStatus');
            info = $('#placeholder #info');
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
            expect(info.text()).to.equal('Saved');
            validKey(firstInput);
            expect(info.text()).to.equal('');
        });
        it('should not reset the state when the user enters a none numeric or . key', function () {
            autosave.save();
            expect(info.text()).to.equal('Saved');
            tabKey(firstInput);
            expect(info.text()).to.equal('Saved');
        });
    });
    describe('Tell the user about form validation errors', function () {
        
        var formgroup;
        
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
            formgroup = firstInput.parent();
            
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
            var data = form.serialize();
            autosave.save();
            expect(ajaxSpy.firstCall.args[0].data).to.equal(data);
        });
        it('should send the data to the url provided', function () {
            autosave.save();
            expect(ajaxSpy.firstCall.args[0].url).to.equal('test');
        });
    });
    
    
    function validKey(element) {
        var e = jQuery.Event("keypress");
        e.which = 50; // # Some key code value
        $(element).trigger(e);
    }
    
    function tabKey(element) {
        var e = jQuery.Event("keypress");
        e.which = 9; // # Some key code value
        $(element).trigger(e);
    }
});
