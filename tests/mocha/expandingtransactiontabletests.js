describe('Expanding Transaction Table Tests', function () {
   
    var placeholder = $('#placeholder'),
        template = $('#template'),
        expandingTransactionTable;
    
    beforeEach(function () {
        placeholder.text('').append(template.clone());
        expandingTransactionTable = new GOVUK.ExpandingTransactionTable('#template .expanding-transaction-table');
    });

    afterEach(function() {
        placeholder.empty();
    });
    
    describe('Expanding', function () {
        it('should make all sections collapsed by default', function () {
            
        });
        it('should open a section when its title is clicked');
        it('should close other sections when you open one');
    });
    
    
    describe('totals', function () {
        describe('hide/save', function () {
            it('should initially the title in the header');
            it('should hide the total in the header when a section is expanded');
            it('should show the total at the bottom of the expanded section when it is expanded');
            it('should show the total in the header when the section is closed again');
        });
        describe('auto total', function () {
            it('should ');
            
            it('should adjust the total in the header when a transaction is changed.');
            it('should adjust the total at the bottom of a section when a transaction is changed');
            it('should ignore transactions that contain a text value');
            it('should update the grand total when a transaction is changed.');
        });
    });
    
    describe('more details', function () {
        describe('show/hide', function () {
            it('should initially hide further info if there is no value');
            it('should initially show further info if there is a value');
            it('should show the further info if a value goes from 0 to something');
            it('should show the further info if a value goes from null to something');
            it('should hide further info if a value goes from a number to 0');
            it('should hide further info if a value goes from a number to null');
        });
        describe('clear further info', function () {
            it('should set a further info value to null if a number goes from something to 0');
            it('should set a further info value to null if a number goes from something to null');
        });
    });
    
    
});
