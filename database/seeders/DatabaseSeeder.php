<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/data/catalog.json'), true);

        // Admin — CHANGE THIS PASSWORD after first login (or set ADMIN_PASSWORD in .env)
        User::updateOrCreate(['email' => 'admin@suratnx.com'], [
            'name' => 'Admin', 'phone' => '+91 98765 43210', 'role' => 'admin',
            'password' => env('ADMIN_PASSWORD', 'admin123'),
        ]);

        // Demo customer (remove in production)
        $demo = User::updateOrCreate(['email' => 'demo@suratnx.com'], [
            'name' => 'Demo User', 'phone' => '9825012345', 'gender' => 'Female', 'role' => 'customer', 'password' => 'demo123',
        ]);
        if (!$demo->addresses()->exists()) {
            $demo->addresses()->create([
                'name' => 'Demo User', 'phone' => '9825012345', 'line1' => '101 Textile Tower, Ring Road',
                'line2' => 'Near Sahara Darwaja', 'landmark' => 'Opp. City Mall', 'city' => 'Surat',
                'state' => 'Gujarat', 'pin' => '395002', 'type' => 'Home', 'is_default' => true,
            ]);
        }

        $cats = [];
        foreach ($data['categories'] as $c) {
            $cats[$c['slug']] = Category::updateOrCreate(['slug' => $c['slug']], $c + ['is_active' => true]);
        }

        foreach ($data['products'] as $p) {
            $cat = $cats[$p['category']];
            unset($p['category']);
            Product::updateOrCreate(['sku' => $p['sku']], $p + [
                'category_id' => $cat->id, 'slug' => Str::slug($p['name']), 'status' => 'active',
            ]);
        }

        foreach ($data['coupons'] as $c) {
            Coupon::updateOrCreate(['code' => $c['code']], $c + ['is_active' => true]);
        }

        Setting::put($data['settings']);
    }
}
