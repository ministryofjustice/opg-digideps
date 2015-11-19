describe('Expanding Transaction Table Tests', function () {
   
    var placeholder = $('#placeholder'),
        template = $('#template'),
        expandingTransactionTable;
    
    beforeEach(function () {
        placeholder.text('').append(template.clone());
        expandingTransactionTable = new GOVUK.ExpandingTransactionTable('#template .expanding-transaction-table');
    });

    afterEach(function() {
        //placeholder.empty();
    });
    
    function getFirstSection() {
        return placeholder.find('.section').first();
    }
    
    
    describe('Expanding', function () {
        it('should make all sections collapsed by default', function () {
            placeholder.find('.detail').each(function (index,element) {
                expect($(element).is(':visible')).to.be.false;
            });      
        });
        it('should open a section when its title is clicked', function () {
            var section = getFirstSection();
            $('.summary', section).trigger('click');
            var visible = $('.detail', section).is(':visible');
            expect(visible).to.be.true;
        });
        it('should close other sections when you open one', function () {
            var sections = $('.section', placeholder);
            
            var first = $(sections[0]);
            var second = $(sections[1]);
        
            $('.summary', first).trigger('click');

            expect($('.detail', first).is(':visible')).to.be.true;
            expect($('.detail', second).is(':visible')).to.be.false;

            $('.summary', second).trigger('click');
        
            expect($('.detail', first).is(':visible')).to.be.false;
            expect($('.detail', second).is(':visible')).to.be.true;
            
        });
    });
    
    
    describe('totals', function () {
        describe('hide/save', function () {
            it('should initially the title in the header', function () {
                var section = getFirstSection();
                expect($('.summary .sub-total', section).is(':visible')).to.be.true;
            });
            it('should hide the total in the header when a section is expanded', function () {
                var section = getFirstSection();
                $('.summary', section).trigger('click');
                expect($('.summary .sub-total', section).is(':visible')).to.be.false;
            });
            it('should show the total at the bottom of the expanded section when it is expanded', function () {
                var section = getFirstSection();
                $('.summary', section).trigger('click');
                expect($('.detail .sub-total', section).is(':visible')).to.be.true;
            });
            it('should show the total in the header when the section is closed again', function () {
                var section = getFirstSection();
                $('.summary', section).trigger('click');
                expect($('.summary .sub-total', section).is(':visible')).to.be.false;
                $('.summary', section).trigger('click');
                expect($('.summary .sub-total', section).is(':visible')).to.be.true;
            });
        });
        describe('auto total', function () {
            it('should adjust the total in the header when a transaction is changed.', function () {
                
                var section = getFirstSection();
                var t1 = $('.transaction:nth-child(1) input.form-control', section);
                var t2 = $('.transaction:nth-child(2) input.form-control', section);
                
                t1.val('1.25').trigger('keyup');
                t2.val('1.25').trigger('keyup');
                
                expect($('.summary .sub-total .value', section).text()).to.equal('2.50');
                
            });
            it('should adjust the total at the bottom of a section when a transaction is changed', function () {
                var section = getFirstSection();
                var t1 = $('.transaction:nth-child(1) input.form-control', section);
                var t2 = $('.transaction:nth-child(2) input.form-control', section);

                t1.val('1.25').trigger('keyup');
                t2.val('1.25').trigger('keyup');

                expect($('.detail .sub-total .value', section).text()).to.equal('2.50');                
            });
            it('should ignore transactions that contain a text value',function () {
                var section = getFirstSection();
                var t1 = $('.transaction:nth-child(1) input.form-control', section);
                var t2 = $('.transaction:nth-child(2) input.form-control', section);
                var t3 = $('.transaction:nth-child(3) input.form-control', section);

                t1.val('1.25').trigger('keyup');
                t2.val('1.25').trigger('keyup');
                t3.val('asd').trigger('keyup');

                expect($('.summary .sub-total .value', section).text()).to.equal('2.50');
            });
            it('should update the grand total when a transaction is changed.', function () {
                var firstSection = $('.section:nth-child(1)', placeholder);
                var secondSection = $('.section:nth-child(2)', placeholder);

                var t11 = $('.transaction:nth-child(1) input.form-control', firstSection);
                var t12 = $('.transaction:nth-child(2) input.form-control', firstSection);

                t11.val('1.25').trigger('keyup');
                t12.val('1.25').trigger('keyup');

                var t21 = $('.transaction:nth-child(1) input.form-control', secondSection);
                var t22 = $('.transaction:nth-child(2) input.form-control', secondSection);

                t21.val('1.25').trigger('keyup');
                t22.val('1.25').trigger('keyup');
                
                expect($('.grand-total .value', placeholder).text()).to.equal('5.00');
                
            });
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
