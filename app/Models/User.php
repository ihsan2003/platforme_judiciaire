<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relations
    public function dossiersCrees()
    {
        return $this->hasMany(DossierJudiciaire::class, 'created_by');
    }

    public function jugementsCrees()
    {
        return $this->hasMany(Jugement::class, 'created_by');
    }

    public function actionsReclamations()
    {
        return $this->hasMany(ActionReclamation::class, 'created_by');
    }

    public function executionsResponsable()
    {
        return $this->hasMany(Execution::class, 'responsable_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_utilisateur');
    }

    // Accesseurs
    public function getNotificationsNonLuesAttribute()
    {
        return $this->notifications()->where('est_lue', false)->count();
    }

    public function getEstAdminAttribute()
    {
        return $this->hasRole('admin');
    }
}