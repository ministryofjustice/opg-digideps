<?php

namespace App\Entity;

interface SynchronisableInterface
{
    const string SYNC_STATUS_QUEUED = 'QUEUED';
    const string SYNC_STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const string SYNC_STATUS_SUCCESS = 'SUCCESS';
    const string SYNC_STATUS_TEMPORARY_ERROR = 'TEMPORARY_ERROR';
    const string SYNC_STATUS_PERMANENT_ERROR = 'PERMANENT_ERROR';
}
