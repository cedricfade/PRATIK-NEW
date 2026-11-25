<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'source_news', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class SourceNews extends Model
{
}
