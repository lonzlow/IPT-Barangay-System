<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Expiry Notifications
    |--------------------------------------------------------------------------
    |
    | When true, users with document-issuance roles are notified by mail when
    | the scheduled expiry command marks documents as expired.
    |
    */

    'expiry_notify' => env('DOCUMENT_EXPIRY_NOTIFY', false),

];
