<?php

namespace AppBundle\Entity;

/**
 * Common functionalities among Report and NDR
 */
interface ReportInterface
{
    // https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
    const TYPE_103 = '103';
    const TYPE_102 = '102';
    const TYPE_104 = '104';
    const TYPE_103_4 = '103-4';
    const TYPE_102_4 = '102-4';

    // PA
    const TYPE_103_6 = '103-6';
    const TYPE_102_6 = '102-6';
    const TYPE_104_6 = '104-6';
    const TYPE_103_4_6 = '103-4-6';
    const TYPE_102_4_6 = '102-4-6';

    // PROF
    const TYPE_103_5 = '103-5';
    const TYPE_102_5 = '102-5';
    const TYPE_104_5 = '104-5';
    const TYPE_103_4_5 = '103-4-5';
    const TYPE_102_4_5 = '102-4-5';
    
    public function getId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return Client
     */
    public function getClient();

    public function createAttachmentName($format);
}
