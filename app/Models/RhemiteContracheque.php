<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhemiteContracheque extends Model
{
    protected $table = 'rhemitecontracheque';
    protected $primaryKey = 'rh85_sequencial';
    public $timestamps = false; // se a tabela não tiver created_at/updated_at

    // MUITO IMPORTANTE → falar para o Laravel que a PK é auto increment
    public $incrementing = true;

    // E que o tipo é inteiro (se for bigint, troque)
    protected $keyType = 'int';

    protected $fillable = [
        'rh85_regist',
        'rh85_anousu',
        'rh85_mesusu',
        'rh85_dataemissao',
        'rh85_horaemissao',
        'rh85_sigla',
        'rh85_codautent',
        'rh85_ip',
        'rh85_externo'
    ];
}
