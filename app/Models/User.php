<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'password',
        'username',
        'directorate_id',
        'city_id',
        'role_id',
        'phone',
        'gender',
        'certificate',
        'courses',
        'evaluation'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function directorate()
    {
        return $this->belongsTo(Directorate::class, 'directorate_id', 'id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
    public function surveys()
    {
        return $this->belongsToMany(Survey::class, 'users_surveys', 'user_id', 'survey_id', 'id', 'id');
    }
    public function isAnswer(Survey $survey)
    {
        $entries = $survey->entries;
        // return $[entries];
        foreach ($entries as $entry) {
            if ($entry->participant_id == $this->id) {
                return true;
            }
            return false;
        }
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }
}
