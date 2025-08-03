<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Laravel\Sanctum\HasApiTokens;

class AuthMeUser extends Model implements Authenticatable
{
    use AuthenticatableTrait, HasApiTokens, HasFactory;

    protected $table = 'authme';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'realname',
        'password',
        'ip',
        'lastlogin',
        'x',
        'y',
        'z',
        'world',
        'regdate',
        'regip',
        'yaw',
        'pitch',
        'email',
        'isLogged'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'regdate' => 'integer',
        'lastlogin' => 'integer',
        'x' => 'float',
        'y' => 'float',
        'z' => 'float',
        'yaw' => 'float',
        'pitch' => 'float',
        'isLogged' => 'boolean'
    ];

    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function getAuthIdentifier()
    {
        return $this->username;
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function getFormattedRegDate(): ?string
    {
        return $this->regdate ? date('Y-m-d H:i:s', $this->regdate / 1000) : null;
    }

    public function getFormattedLastLogin(): ?string
    {
        return $this->lastlogin ? date('Y-m-d H:i:s', $this->lastlogin / 1000) : null;
    }

    public function isOnline(): bool
    {
        return $this->isLogged;
    }

    public function getCoordinates(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
            'world' => $this->world,
            'yaw' => $this->yaw,
            'pitch' => $this->pitch
        ];
    }
}
