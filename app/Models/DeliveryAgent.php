<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAgent extends Model
{
    protected $table = 'delivery_agents';
    protected $primaryKey = 'DeliveryAgentID';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'IsVerified' => 'boolean',
        'AdminVerified' => 'boolean',
        'Attachment' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'DeliveryAgentID', 'DeliveryAgentID');
    }

    /**
     * Find the most suitable available agent for a given delivery address.
     * Logic: Proximity (10km) -> ServiceArea Match (City/Region) -> Regional Fallback -> Random in same city
     */
    public static function findBestForAddress($address, $lat = null, $lng = null)
    {
        // 1. Try Proximity (if coordinates available)
        if ($lat && $lng) {
            $agent = self::select('delivery_agents.*')
                ->selectRaw("( 6371 * acos( cos( radians(?) ) * cos( radians( Latitude ) ) * cos( radians( Longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( Latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
                ->where('Status', 'Available')
                ->whereNotNull('Latitude')
                ->whereNotNull('Longitude')
                ->having('distance', '<=', 15) // Increased to 15km for better coverage
                ->orderBy('distance')
                ->first();
            
            if ($agent) return $agent;
        }

        // 2. Try ServiceArea String Match (e.g. "Cairo", "Alexandria")
        $cleanAddr = strtolower($address);
        $cities = ['cairo', 'giza', 'alexandria', 'mansoura', 'tanta', 'sohag', 'assiut', 'ismailia', 'suez', 'port said', 'bani sweif', 'fayoum', 'qena', 'aswan', 'luxor'];
        $foundCity = null;
        foreach ($cities as $city) {
            if (str_contains($cleanAddr, $city)) {
                $foundCity = $city;
                break;
            }
        }

        if ($foundCity) {
            $agent = self::where('Status', 'Available')
                ->where(function($q) use ($foundCity) {
                    $q->where('ServiceArea', 'LIKE', "%{$foundCity}%")
                      ->orWhere('ServiceArea', 'LIKE', "%" . ucwords($foundCity) . "%");
                })
                ->inRandomOrder()
                ->first();
            
            if ($agent) return $agent;
        }

        // 3. Last Resort Fallback: Any available agent in the general area if possible, 
        // otherwise just stay null to allow manual assignment rather than sending it to the wrong city
        return self::where('Status', 'Available')
            ->whereNotNull('ServiceArea') // Prefer agents who at least set an area
            ->inRandomOrder()
            ->first();
    }
}
