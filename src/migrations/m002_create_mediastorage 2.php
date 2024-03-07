<?php

use VulcanPhp\Core\Database\Interfaces\IMigration;
use VulcanPhp\Core\Database\Schema\Schema;

return new class implements IMigration
{

    public function up(): string
    {
        return Schema::create('mediastorage')
            ->id()
            ->foreignId('parent')->nullable()->constrained('mediastorage', 'id')->onUpdate('cascade')->onDelete('cascade')
            ->string('title')->key('file_title')
            ->string('type', 50)->key('file_type')
            ->text('content')
            ->timestamp('created_at')
            ->build();
    }

    public function down(): string
    {
        return Schema::drop('mediastorage');
    }
};
