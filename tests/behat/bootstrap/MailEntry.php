<?php

namespace DigidepsBehat;

/**
 * Contains mail data, constructed on a single mail log,
 * for instance written by Zend_Mail_Transport_File.
 */
class MailEntry
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $raw;

    /**
     * @param string $filename Mail content
     */
    public function __construct($content)
    {
        $this->raw = $content;

        foreach (explode("\n", $content) as $line) {
            $this->processLine($line);
        }
    }

    /**
     * @param string $line
     */
    protected function processLine($line)
    {
        preg_match('#(\w+): (.+)#i', $line, $m);
        if (empty($m[2])) {
            return;
        }
        $value = trim($m[2], "\n \r\n");
        switch ($m[1]) {
            case 'Subject';
                if (!$this->subject) {
                    $this->subject = $value;
                }
                break;
            case 'To':
                if (!$this->to) {
                    $this->to = $value;
                }
                break;
        }
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
