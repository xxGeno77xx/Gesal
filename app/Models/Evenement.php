<?php

namespace App\Models;

use App\Models\User;
use App\Support\Classes\StatesClass;
use Buildix\Timex\Traits\TimexTrait;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evenement extends Model
{
    use HasUuids;
    use TimexTrait;



    protected $guarded = [];

    protected $casts = [
        'start' => 'date',
        'end' => 'date',
        'isAllDay' => 'boolean',
        'participants' => 'array',
        'attachments' => 'array',
        'salle_id'=>'integer',
    ];

    public function getTable()
    {
        return config('timex.tables.event.name', "timex_events");
    }

    public function __construct(array $attributes = [])
    {
        $attributes['organizer'] = \Auth::id();
        $attributes['Statut'] = StatesClass::Suspended()->value;
        $attributes['category'] = 'primary';

        parent::__construct($attributes);
    }

    public function category()
    {
        return $this->hasOne(self::getCategoryModel());
    }

    public function salle()
    {
        return $this->hasOne(Salle::class);
    }

}
