<?php

use VulcanPhp\Core\Database\Interfaces\IMigration;
use VulcanPhp\Core\Database\Schema\Schema;

return new class implements IMigration
{

    public function up(): string
    {
        return Schema::create('pages')
            ->id()
            ->string('title', 100)->key('page')
            ->string('slug', 150)->unique()
            ->text('content')->nullable()
            ->build();
    }

    public function down(): string
    {
        return Schema::drop('pages');
    }
};
