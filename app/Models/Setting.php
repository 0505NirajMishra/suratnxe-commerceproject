<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Key/value store for admin > Settings (name, tagline, shipping fees, GST ...). */
class Setting extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'name' => 'Surat NX', 'tagline' => 'The Textile House', 'email' => 'suratnxllp@gmail.com',
        'phone' => '+91 70412 62555', 'address' => '106 AIIJEE Empire, next to Bharat Cancer Hospital, Saroli, Surat, Gujarat 395010',
        'whatsapp' => '+917041262555',
        'map_url' => 'https://www.google.com/maps/search/?api=1&query=106+AIIJEE+Empire%2C+Saroli%2C+Surat%2C+Gujarat+395010',
        'business_hours' => "Monday: 11:00 AM – 12:00 AM\nTuesday–Saturday: 12:00 AM–8:00 AM, 11:00 AM–12:00 AM\nSunday: 12:00 AM–8:00 AM\nHours might differ on holidays.",
        'youtube_video' => 'https://www.youtube.com/shorts/76itAN63bVI',
        'social_facebook' => 'https://www.facebook.com/profile.php?id=61565919966697',
        'social_instagram' => 'https://www.instagram.com/suratnx/',
        'social_x' => 'https://x.com/SuratNx',
        'social_linkedin' => 'https://www.linkedin.com/in/suratnx/',
        'social_youtube' => 'https://www.youtube.com/channel/UCvzSU9hkPTNybTQ0VyGxh1Q',
        'free_ship' => 2000, 'ship_fee' => 99, 'express_fee' => 199, 'gst' => 5,
        'standard_delivery_min_days' => 3, 'standard_delivery_max_days' => 6,
        'express_delivery_min_days' => 1, 'express_delivery_max_days' => 3,
        'return_window_days' => 7,
        'hero_title' => 'The Fabric Behind Beautiful Fashion',
        'hero_subtitle' => 'Luxurious fabrics. Timeless quality. Designed for your signature creations.',
        'about_intro' => "Rooted in Surat, India's textile capital, Surat NX brings thoughtfully selected fabrics to designers, boutiques and creators.",
        'policy_shipping' => 'Orders are processed after confirmation. Dispatch and delivery timelines can vary by stock, destination and courier. Use your order number and email on the Track Order page for updates.',
        'policy_returns' => 'Please inspect your fabric order on delivery and contact our team promptly if the item is incorrect or damaged. Return eligibility and resolution are confirmed by our support team according to the order circumstances.',
        'policy_privacy' => 'We use the information you provide to process orders, provide support and maintain your account. We do not display customer account information publicly.',
        'policy_terms' => 'Product images and descriptions are provided to help customers select fabrics. Colour appearance may vary by display. Prices, availability and shipping charges are shown at checkout.',
        'hero_kicker' => 'Premium textile collection',
        'feature_quality_title' => 'Premium Quality', 'feature_quality_copy' => 'Finest fabrics with exceptional finish',
        'feature_custom_title' => 'Custom Orders', 'feature_custom_copy' => 'Get fabrics tailored to your vision',
        'feature_shipping_title' => 'Reliable Shipping', 'feature_shipping_copy' => 'Carefully packed and dispatched',
        'home_categories_title' => 'Shop by Category', 'home_categories_link' => 'View all categories',
        'home_featured_title' => 'Featured Fabrics', 'home_featured_copy' => 'Discover pieces chosen for quality, texture and finish.',
        'home_custom_title' => 'Need a fabric made your way?', 'home_custom_copy' => 'Tell us the colour, weave and quantity. Our team works with Surat mills to match your design, from a sample length to bulk boutique orders.', 'home_custom_button' => 'Request a custom order',
        'home_why_title' => 'Why designers choose Surat NX', 'home_why_copy' => 'Sourced in Surat and checked before it ships.',
        'why_source_title' => 'Sourced in Surat', 'why_source_copy' => 'We buy directly from mills and weavers, so you get honest pricing.',
        'why_quality_title' => 'Checked every metre', 'why_quality_copy' => 'Each roll is inspected for colour, weave and finish before dispatch.',
        'why_shipping_title' => 'Careful dispatch', 'why_shipping_copy' => 'Orders are packed with care and status updates are available.',
        'why_payment_title' => 'Pay your way', 'why_payment_copy' => 'Choose an available payment method at checkout.',
        'home_bulk_title' => 'Bulk or boutique order?', 'home_bulk_copy' => 'Share your requirement and get wholesale rates within one working day.', 'home_bulk_button' => 'Contact our team',
        'about_title' => 'Every beautiful fashion story begins with fabric.',
        'about_quality_title' => 'Quality first', 'about_quality_copy' => 'We focus on fabric feel, finish and consistency.',
        'about_collection_title' => 'Curated collection', 'about_collection_copy' => 'From everyday essentials to occasion wear, discover fabrics for your next creation.',
        'about_service_title' => 'Customer-first service', 'about_service_copy' => 'Our team helps with product questions, bulk requirements and order updates.',
        'about_cta_title' => "Let's create something beautiful", 'about_cta_copy' => "Explore our collection or tell us what you're looking for.",
        'footer_description' => 'Premium fabrics from the textile capital of India. We supply designers, boutiques and homes with fabrics that are finished well and priced fairly.',
        'location' => 'Saroli, Surat, Gujarat, India',
    ];

    public static function all_(): array
    {
        return array_merge(
            self::DEFAULTS,
            Cache::rememberForever('settings', fn () => self::query()->pluck('value', 'key')->all())
        );
    }

    public static function get(string $key, $default = null)
    {
        return self::all_()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $k => $v) {
            self::updateOrCreate(['key' => $k], ['value' => $v]);
        }
        Cache::forever('settings', self::query()->pluck('value', 'key')->all());
    }
}
