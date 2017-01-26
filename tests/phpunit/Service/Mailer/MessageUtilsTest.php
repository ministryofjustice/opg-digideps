<?php

namespace AppBundle\Service\Mailer;

class MessageUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testmessageToArray()
    {
        $message = $this->getMockForAbstractClass('\Swift_Mime_Message', ['getSubject']);

        $child = $this->getMock('stdClass', ['getBody', 'getContentType']);
        $child->expects($this->once())->method('getBody')->will($this->returnValue('b'));
        $child->expects($this->once())->method('getContentType')->will($this->returnValue('ct'));

        $message->expects($this->once())
                ->method('getSubject')
                ->will($this->returnValue('s'));

        $message->expects($this->once())
                ->method('getChildren')
                ->will($this->returnValue([$child]));

        $array = MessageUtils::messageToArray($message);

        $this->assertEquals('s', $array['subject']);
        $this->assertEquals(base64_encode('b'), $array['parts'][0]['body']);
        $this->assertEquals('ct', $array['parts'][0]['contentType']);
    }

    public function testarrayToMessage()
    {
        $array = [
            'subject' => 's',
            'parts' => [
                ['body' => 'b', 'contentType' => 'c'],
            ],
        ];
        $message = MessageUtils::arrayToMessage($array);

        $this->assertInstanceOf('\Swift_Message', $message);
        $this->assertEquals('s', $message->getSubject());
        $this->assertEquals('b', $message->getChildren()[0]->getBody());
        $this->assertEquals('c', $message->getChildren()[0]->getContentType());
    }

    public function testarrayToString()
    {
        $message = $this->getMockForAbstractClass('\Swift_Mime_Message', ['getSubject']);

        $child1 = $this->getMock('stdClass', ['getBody', 'getContentType']);
        $child1->expects($this->once())->method('getBody')->will($this->returnValue('<b>test</b>'));
        $child1->expects($this->once())->method('getContentType')->will($this->returnValue('text/html'));

        $child2 = $this->getMock('stdClass', ['getBody', 'getContentType']);
        $child2->expects($this->once())->method('getBody')->will($this->returnValue('testPlain'));
        $child2->expects($this->once())->method('getContentType')->will($this->returnValue('text/plain'));

        $message->expects($this->once())
                ->method('getSubject')
                ->will($this->returnValue('subject1'));

        $message->expects($this->once())
                ->method('getChildren')
                ->will($this->returnValue([$child1, $child2]));

        $messageString = MessageUtils::messageToString($message);

        $this->assertContains('subject1', $messageString);
        $this->assertContains('<b>test</b>', $messageString);
        $this->assertContains('testPlain', $messageString);
    }
}
