Feature: components / Expanding Textarea
    On javascript enablbed browsers the textareas should grow ads a user enters more 
    text so that the information is easily viewable and easy to read.
    
    
    @javascript
    Scenario: An expanding textarea looks like a normal textarea when it is loaded
        Given I am on the textarea test page
        Then the "test-textarea" element has a height between 120 px and 140 px
        
    @javascript
    Scenario: An expanding text area looks bigger than normal when the page and the value is lots of text
        Given I am on the textarea test page
        Then the "test-textarea-long" element has a height greater than 140 px
        
    @javascript
    Scenario: An expanding text area grows then a user enters in more than 5 lines of text
        Given I am on the textarea test page
        When I fill in "test-textarea" with "lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob whatlorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what lorem ipsum thingy bob what"
        Then the "test-textarea" element has a height greater than 140 px
        
    