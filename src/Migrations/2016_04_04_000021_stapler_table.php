<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class StaplerTable extends Migration {
    
    /**
     * Make changes to the table.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stapler_files', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('fileable_id')->unsigned();
            $table->string('fileable_type')->index();
            $table->string('fileable_field')->index();
            $table->string('file_file_name')->nullable();
            $table->integer('file_file_size')->nullable();
            $table->string('file_content_type')->nullable();
            $table->timestamp('file_created_at')->nullable();
            $table->timestamp('file_updated_at')->nullable();
            $table->timestamps();
            $table->index(['fileable_id', 'fileable_type']);
            $table->index(['fileable_id', 'fileable_type', 'fileable_field']);
        });

    }

    /**
     * Revert the changes to the table.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('stapler_files');
    }

}