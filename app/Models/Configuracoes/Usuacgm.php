<?php

namespace App\Models\Configuracoes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Usuacgm extends Model {
    public $timestamps = false;

    protected $table = 'configuracoes.db_usuacgm';

    public $incrementing = false;

    protected $fillable = [
        'cgmlogin',
        'id_usuario'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario')->select(['id_usuario', 'cgmlogin']);
    }
}
