<?php

namespace AppBundle\Service\Mailer;

use Swift_Message;
use Swift_Mime_SimpleMessage;

/**
 * \Swift_Message utils.
 */
class MessageUtils
{
    protected static $fieldsToSerialize = [
        'to',
        'from',
        'bcc',
        'cc',
        'replyTo',
        'returnPath',
        'subject',
        'body',
        'sender',
    ];

    /**
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    public static function messageToArray(Swift_Mime_SimpleMessage $message)
    {
        $ret = [];
        foreach (self::$fieldsToSerialize as $field) {
            $method = 'get' . ucfirst($field);
            $ret[$field] = $message->$method();
        }

        // add parts
        $ret['parts'] = [];
        foreach ($message->getChildren() as $child) {
            $ret['parts'][] = [
                'body' => base64_encode($child->getBody()),
                'contentType' => $child->getContentType(),
            ];
        }

        return $ret;
    }

    /**
     * @param array $array
     *
     * @return Swift_Message
     */
    public static function arrayToMessage($array)
    {
        $message = new Swift_Message();

        foreach (self::$fieldsToSerialize as $field) {
            if (!empty($array[$field])) {
                $method = 'set' . ucfirst($field);
                $message->$method($array[$field]);
            }
        }

        foreach ((array) $array['parts'] as $part) {
            $message->addPart($part['body'], $part['contentType']);
        }

        return $message;
    }

    /**
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    public static function messageToString(Swift_Mime_SimpleMessage $message)
    {
        $ret = '';
        foreach (self::$fieldsToSerialize as $field) {
            $method = 'get' . ucfirst($field);
            $methndreturned = $message->$method();
            if (is_array($methndreturned)) {
                $methndreturned = print_r($methndreturned, true);
            }
            $ret .= sprintf("%s: %s\n", $field, $methndreturned);
        }

        // add parts
        foreach ($message->getChildren() as $k => $child) {
            $ret .= sprintf("PART $k -----\n Body: %s\n ContentType:%s\n",
                $child->getBody(),
                $child->getContentType()
            );
        }

        $ret .= "--------------------------\n";

        return $ret;
    }
}
