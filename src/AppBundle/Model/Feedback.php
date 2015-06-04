<?php
namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;

class Feedback
{
    /**
     * @JMS\Type("string")
     */
    private $difficulty;
    
    /**
     *
     * @JMS\Type("string")
     */
    private $ideas;
    
    /**
     * @JMS\Type("string")
     */
    private $satisfactionLevel;
    

    public function getDifficulty()
    {
        return $this->difficulty;
    }
    
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
        return $this;
    }
    
    public function getIdeas()
    {
        return $this->ideas;
    }
    
    public function setIdeas($ideas)
    {
        $this->ideas = $ideas;
        return $this;
    }
    
    public function getSatisfactionLevel()
    {
        return $this->satisfactionLevel;
    }
    
    public function setSatisfactionLevel($satisfactionLevel)
    {
        $this->satisfactionLevel = $satisfactionLevel;
        return $this;
    }
}
