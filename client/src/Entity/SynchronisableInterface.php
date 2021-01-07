<?php

namespace App\Entity;

interface SynchronisableInterface
{
    const SYNC_STATUS_QUEUED = 'QUEUED';
    const SYNC_STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const SYNC_STATUS_SUCCESS = 'SUCCESS';
    const SYNC_STATUS_TEMPORARY_ERROR = 'TEMPORARY_ERROR';
    const SYNC_STATUS_PERMANENT_ERROR = 'PERMANENT_ERROR';
}
