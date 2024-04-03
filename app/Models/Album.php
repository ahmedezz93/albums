<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Album extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }


    public static function boot() {
        parent::boot();
        static::creating(function (Album $album) {
            $album->{'uuid'} = (string) Str::orderedUuid();
        });
    }
}
