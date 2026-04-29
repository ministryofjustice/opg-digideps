<?php

namespace OPG\Digideps\Backend\Entity;

interface SynchronisableInterface
{
    public const string SYNC_STATUS_QUEUED = 'QUEUED';
    public const string SYNC_STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const string SYNC_STATUS_SUCCESS = 'SUCCESS';
    public const string SYNC_STATUS_TEMPORARY_ERROR = 'TEMPORARY_ERROR';
    public const string SYNC_STATUS_PERMANENT_ERROR = 'PERMANENT_ERROR';
}
