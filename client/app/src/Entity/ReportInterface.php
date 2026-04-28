<?php

namespace OPG\Digideps\Frontend\Entity;

interface ReportInterface
{
    // https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
    public const string LAY_PFA_LOW_ASSETS_TYPE = '103';
    public const string LAY_PFA_HIGH_ASSETS_TYPE = '102';
    public const string LAY_HW_TYPE = '104';
    public const string LAY_COMBINED_LOW_ASSETS_TYPE = '103-4';
    public const string LAY_COMBINED_HIGH_ASSETS_TYPE = '102-4';

    // PA
    public const string PA_PFA_LOW_ASSETS_TYPE = '103-6';
    public const string PA_PFA_HIGH_ASSETS_TYPE = '102-6';
    public const string PA_HW_TYPE = '104-6';
    public const string PA_COMBINED_LOW_ASSETS_TYPE = '103-4-6';
    public const string PA_COMBINED_HIGH_ASSETS_TYPE = '102-4-6';

    // PROF
    public const string PROF_PFA_LOW_ASSETS_TYPE = '103-5';
    public const string PROF_PFA_HIGH_ASSETS_TYPE = '102-5';
    public const string PROF_HW_TYPE = '104-5';
    public const string PROF_COMBINED_LOW_ASSETS_TYPE = '103-4-5';
    public const string PROF_COMBINED_HIGH_ASSETS_TYPE = '102-4-5';

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
