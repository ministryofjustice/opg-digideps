<?php

namespace App\Entity;

interface SynchronisableInterface
{
    public const SYNC_STATUS_QUEUED = 'QUEUED';
    public const SYNC_STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const SYNC_STATUS_SUCCESS = 'SUCCESS';
    public const SYNC_STATUS_TEMPORARY_ERROR = 'TEMPORARY_ERROR';
    public const SYNC_STATUS_PERMANENT_ERROR = 'PERMANENT_ERROR';
}
