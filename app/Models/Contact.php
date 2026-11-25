<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/** Messages envoyés via les formulaires de contact / "se faire connaître". */
#[Table(name: 'contacts', timestamps: false)]
#[Guarded(['id'])]
class Contact extends Model
{
}
