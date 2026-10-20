<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'email')
            ->where('value', 'info@suratnx.com')
            ->update(['value' => 'suratnxllp@gmail.com']);
        Cache::forget('settings');
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'email')
            ->where('value', 'suratnxllp@gmail.com')
            ->update(['value' => 'info@suratnx.com']);
        Cache::forget('settings');
    }
};
