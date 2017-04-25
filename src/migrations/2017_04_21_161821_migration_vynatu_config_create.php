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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrationVynatuConfigCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            config('database_config.table'),
            function (Blueprint $table) {
                $table->string('item')->unique();

                if (config('database_config.json')) {
                    $table->json('value')->nullable();
                } else {
                    $table->text('value')->nullable();
                }


                $table->engine = 'InnoDB';
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(config('config.table'));
    }
}
