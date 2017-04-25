<?php
/**
 *
 * This file is part of Vynatu/Database-Config.
 *
 * (c) 2017 Vynatu Cyberlabs, Inc. <felix@vynatu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


return [

    'table'              => 'config',

    // Wether or not to use MySQL 5.7's JSON column type
    'json'               => false,
    /*
    |--------------------------------------------------------------------------
    | Constraints
    |--------------------------------------------------------------------------
    |
    | Here you may specify the constraints for config. Only items in the
    | constraints array will be fetched from database.
    */
    'enable_constraints' => false,

    'constraints' => [
        // 'mail.driver',
    ],
];