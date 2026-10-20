<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $replaceDefaults = [
            'phone' => [
                '+91 98765 43210' => '+91 70412 62555',
            ],
            'address' => [
                'Textile Market, Ring Road, Surat, Gujarat 395002' => '106 AIIJEE Empire, next to Bharat Cancer Hospital, Saroli, Surat, Gujarat 395010',
            ],
            'location' => [
                'Surat, Gujarat, India' => 'Saroli, Surat, Gujarat, India',
            ],
        ];

        foreach ($replaceDefaults as $key => $values) {
            $setting = DB::table('settings')->where('key', $key)->first();
            if (!$setting) {
                continue;
            }

            foreach ($values as $old => $new) {
                if ($setting->value === $old) {
                    DB::table('settings')->where('key', $key)->update(['value' => $new]);
                    break;
                }
            }
        }

        $defaults = [
            'whatsapp' => '+917041262555',
            'map_url' => 'https://www.google.com/maps/search/?api=1&query=106+AIIJEE+Empire%2C+Saroli%2C+Surat%2C+Gujarat+395010',
            'business_hours' => "Monday: 11:00 AM – 12:00 AM\nTuesday–Saturday: 12:00 AM–8:00 AM, 11:00 AM–12:00 AM\nSunday: 12:00 AM–8:00 AM\nHours might differ on holidays.",
            'youtube_video' => 'https://www.youtube.com/shorts/76itAN63bVI',
            'social_facebook' => 'https://www.facebook.com/profile.php?id=61565919966697',
            'social_instagram' => 'https://www.instagram.com/suratnx/',
            'social_x' => 'https://x.com/SuratNx',
            'social_linkedin' => 'https://www.linkedin.com/in/suratnx/',
            'social_youtube' => 'https://www.youtube.com/channel/UCvzSU9hkPTNybTQ0VyGxh1Q',
        ];

        foreach ($defaults as $key => $value) {
            $existing = DB::table('settings')->where('key', $key)->first();
            if (!$existing || trim((string) $existing->value) === '') {
                DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
            }
        }

        Cache::forget('settings');
    }

    public function down(): void
    {
        Cache::forget('settings');
    }
};
