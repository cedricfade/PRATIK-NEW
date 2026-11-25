<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'natures', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class Nature extends Model
{
}
