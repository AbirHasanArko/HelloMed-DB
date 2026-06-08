<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityRoom extends Model
{
    protected $table = 'facility_rooms';

    protected $fillable = [
        'room_number',
        'room_type',
        'capacity',
        'is_active',
    ];

    public function bookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }
}
