<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'departements', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class Departement extends Model
{
}
