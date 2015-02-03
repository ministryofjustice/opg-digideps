<?php
namespace AppBundle\Mailer\Utils;

use \Mockery as m;

class MessageUtilsTest extends \PHPUnit_Framework_TestCase
{ 
    public function testmessageToArray()
    {
        $message = $this->getMockForAbstractClass('\Swift_Mime_Message', array('getSubject'));
        
        $child = $this->getMock('stdClass', array('getBody', 'getContentType'));
        $child->expects($this->once())->method('getBody')->will($this->returnValue('b'));
        $child->expects($this->once())->method('getContentType')->will($this->returnValue('ct'));
        
        $message->expects($this->once())
                ->method('getSubject')
                ->will($this->returnValue('s'));
        
        $message->expects($this->once())
                ->method('getChildren')
                ->will($this->returnValue(array($child)));
                
        $array = MessageUtils::messageToArray($message);
        
        $this->assertEquals('s', $array['subject']);
        $this->assertEquals('b', $array['parts'][0]['body']);
        $this->assertEquals('ct', $array['parts'][0]['contentType']);
    }
    
    public function testarrayToMessage()
    {
        $array = array(
            'subject'=>'s',
            'parts'=> array(
                array('body' => 'b', 'contentType' => 'c')
            )
        );
        $message = MessageUtils::arrayToMessage($array);
        
        $this->assertInstanceOf('\Swift_Message', $message);
        $this->assertEquals('s', $message->getSubject());
        $this->assertEquals('b', $message->getChildren()[0]->getBody());
        $this->assertEquals('c', $message->getChildren()[0]->getContentType());
    }
    
}