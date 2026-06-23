<?php

return [

    /*
    |--------------------------------------------------------------------------
    | On-Call Rotation Start Date
    |--------------------------------------------------------------------------
    |
    | The Monday that begins week 0 of the on-call rotation. Team members are
    | assigned in ascending id order (see TeamMemberSeeder). This value must
    | be a Monday for correct week-index calculations.
    |
    */

    'start_date' => env('ONCALL_START_DATE', '2025-01-06'),

];
