<?php

use VulcanPhp\Core\Database\Interfaces\IMigration;
use VulcanPhp\Core\Database\Schema\Schema;

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create('options')
            ->id()
            ->string('name', 250)->nullable()->key('meta_name')
            ->string('type', 150)->nullable()->key('meta_type')
            ->text('value')->nullable()
            ->build();
    }

    public function down(): string
    {
        return Schema::drop('options');
    }
};
