<?php

namespace App\Entity;

/**
 * Common functionalities among Report and NDR.
 */
interface ReportInterface
{
    // https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
    const LAY_PFA_LOW_ASSETS_TYPE = '103';
    const LAY_PFA_HIGH_ASSETS_TYPE = '102';
    const LAY_HW_TYPE = '104';
    const LAY_COMBINED_LOW_ASSETS_TYPE = '103-4';
    const LAY_COMBINED_HIGH_ASSETS_TYPE = '102-4';

    // PA
    const PA_PFA_LOW_ASSETS_TYPE = '103-6';
    const PA_PFA_HIGH_ASSETS_TYPE = '102-6';
    const PA_HW_TYPE = '104-6';
    const PA_COMBINED_LOW_ASSETS_TYPE = '103-4-6';
    const PA_COMBINED_HIGH_ASSETS_TYPE = '102-4-6';

    // PROF
    const PROF_PFA_LOW_ASSETS_TYPE = '103-5';
    const PROF_PFA_HIGH_ASSETS_TYPE = '102-5';
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
