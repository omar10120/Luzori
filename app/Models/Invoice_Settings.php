<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;

class Invoice_Settings extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    public $timestamps = false;

    protected $table = 'invoice_settings';
    protected $fillable = [
		'phone_number_1',
		'phone_number_2',
		'phone_number_3',
		'emirate',
		'tax_number',
	];
}
