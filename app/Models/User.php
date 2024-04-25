<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Conversation;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'barcode',
        'referrer_id',
        'profileprogess_id',
        'device_token',
        'email_verification_code',
        'email_verification_code_expires',
        'email_verified_at'

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
        'user_type'=>'string'
    ];
    public function profileprogress()
    {
        return $this->hasOne(ProfileProgress::class, 'id', 'profileprogess_id')->with('user_details_No');
    }

    public function userLinks()
    {
        return $this->hasMany(UserLink::class);
    }

    // Relationship: Each user has many link types through user links
    public function linkTypes()
    {
        return $this->hasManyThrough(LinkType::class, UserLink::class);
    }



    public function filmmakers()
    {
        return $this->hasMany(FilmMaker::class);
    }
    public function invitations()
    {
        return $this->hasMany(UserInvite::class,);
    }
    /**
     * A user has a referrer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id', 'id');
    }

    /**
     * A user has many referrals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id', 'id');
    }
    public function savedFeeds()
    {
        return $this->hasMany(SavedFeed::class);
    }
    public function feeds()
    {
        return $this->hasMany(UserDetail::class);
    }
    //merging for all friends
    protected function conversations()
    {
        return $this->started_chat_with->merge($this->responded_to_chat);
    }

    public function started_chat_with()
    {
        return $this->belongsToMany(User::class, Conversation::class, 'sender_id', 'receiver_id')->orderByDesc('last_message_at');
    }

    public function responded_to_chat()
    {
        return $this->belongsToMany(User::class, Conversation::class, 'receiver_id', 'sender_id')->orderByDesc('last_message_at');
    }

    // getting all conversations
    public function getConversationsAttribute()
    {
        if (!array_key_exists('conversations', $this->relations)) $this->loadConversations();

        return $this->getRelation('conversations');
    }

    // loading conversations
    protected function loadConversations()
    {
        if (!array_key_exists('conversations', $this->relations)) {
            $conversations = $this->conversations();

            $this->setRelation('conversations', $conversations);
        }
    }

    public static function generateRandomCode(): int
    {
        return random_int(100000, 999999); // generate random code of six digits
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
    public function device_token()
    {
        return $this->devices->pluck('device_token')->all();
    }
    public function hasDevice($token)
    {
        return $this->devices->contains('device_token', $token);
    }
    public function hasAppInstalled()
    {
        return $this->app_installed;
    }
    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }
    public function getdata()
    {
        $rs = [ $this->id, auth()->user()->id];
        $data['id'] = auth()->user()->id;
        $data['channel_id'] = Conversation::whereIn('sender_id',$rs)->whereIn('receiver_id',$rs)->value('channel_id');
        $data['profile_image'] = FilmMaker::where('user_id',$this->id )->value('profile_image');
        $data['name'] =FilmMaker::where('user_id',$this->id )->value('full_name');
        $data['muted'] =  Conversation::where('sender_id', auth()->id())->where('receiver_id', $this->id)->value('sender_muted');
        $data['company_name'] =  FilmMaker::where('user_id',$this->id )->value('compnay_name');
        return $data;
    }
}
