<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSideBarToCustomRoutes extends Migration
{
    public function up()
    {
        $this->schema->table('routes', function (Blueprint $table) {
            $table->boolean('sidebar')->after('js')->default(0);
            $table->boolean('header')->after('sidebar')->default(0);
            $table->text('header_text')->after('header')->nullable();
            $table->string('header_image')->after('header_text')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('routes', function (Blueprint $table) {
            $table->dropColumn('sidebar');
            $table->dropColumn('header');
            $table->dropColumn('header_text');
            $table->dropColumn('header_image');
        });
    }
}
