<?php

namespace App\Services;

use App\Models\Info;

class InfoService
{
    public function first()
    {
        return Info::first();
    }
}
