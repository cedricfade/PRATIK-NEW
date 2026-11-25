<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'papier_administratif_pieces', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class PapierAdministratifPiece extends Model
{
}
