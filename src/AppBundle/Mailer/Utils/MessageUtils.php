<?php
namespace AppBundle\Mailer\Utils;

/**
 * \Swift_Message utils
 */
class MessageUtils
{
    /**
     */
    protected static $fieldsToSerialize = array(
        'to', 
        'from', 
        'bcc', 
        'cc', 
        'replyTo', 
        'returnPath',
        'subject', 
        'body',
        'sender'
    );
    
    /**
     * @param Swift_Mime_Message $message
     *
     * @return array
     */
    public static function messageToArray(\Swift_Mime_Message $message)
    {
        $ret = array();
        foreach (self::$fieldsToSerialize as $field) {
            $method = "get".ucfirst($field);
            $ret[$field] = $message->$method();
        }
        
        // add parts
        $ret['parts'] = array();
        foreach ($message->getChildren() as $child) {
            $ret['parts'][] = array(
                'body' => $child->getBody(),
                'contentType' => $child->getContentType(),
            );
        }
        
        return $ret;
    }
    
    /**
     * @param array $array
     * 
     * @return \Swift_Mime_Message
     */
    public static function arrayToMessage($array)
    {
        $message = new \Swift_Message;
        
        foreach (self::$fieldsToSerialize as $field) {
            if (!empty($array[$field])) {
                $method = "set".ucfirst($field);
                $message->$method($array[$field]);
            }
        }
        
        foreach ((array)$array['parts'] as $part) {
            $message->addPart($part['body'], $part['contentType']);
        }
        
        return $message;
    }
    
}