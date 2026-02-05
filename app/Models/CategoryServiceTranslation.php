<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryServiceTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'description', 'keywords'];
}
