<?php

namespace Manager\Data\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    const TABLE = "stores";

    protected $table = 'stores';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
