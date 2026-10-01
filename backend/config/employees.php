<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Employee Login Password
    |--------------------------------------------------------------------------
    |
    | The password assigned to an employee login when it is provisioned
    | automatically during employee creation. Every account created this way
    | shares this single known password, so it should be changed (or disabled
    | entirely) once the initial logins have been handed over.
    |
    */

    'default_password' => env('EMPLOYEE_DEFAULT_PASSWORD', 'password123'),

];