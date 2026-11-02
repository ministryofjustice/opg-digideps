<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Mailer;

// keys for different email addresses in the environment;
// see client/docker/app/config/generate_parameters_yml.sh for the list
enum MailerEmailAddress: string
{
    case UPDATE_ADDRESS_PFA_LAY = 'update_address_pfa_lay';
    case UPDATE_ADDRESS_PFA_PRO = 'update_address_pfa_pro';
    case UPDATE_ADDRESS_PFA_PA = 'update_address_pfa_pa';
    case UPDATE_ADDRESS_HW = 'update_address_hw';
}
