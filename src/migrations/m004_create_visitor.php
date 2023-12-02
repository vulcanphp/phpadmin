<?php

use VulcanPhp\Core\Database\Interfaces\IMigration;
use VulcanPhp\Core\Database\Schema\Schema;

return new class implements IMigration
{

    public function up(): string
    {
        return Schema::create('visitors')
            ->id()
            ->integer('ip')->unsigned()->key('visitor_ip')
            ->string('country', 2)->key('visitor_country')
            ->string('os', 25)->nullable()->key('visitor_os')
            ->string('device', 25)->nullable()->key('visitor_device')
            ->string('browser', 50)->nullable()->key('visitor_browser')
            ->string('page', 255)->nullable()->key('visitor_page')
            ->string('referer', 50)->nullable()->key('visitor_referer')
            ->date('date')->nullable()->key('visitor_date')
            ->build();
    }

    public function down(): string
    {
        return Schema::drop('visitors');
    }
};
