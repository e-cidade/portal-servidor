<?php

namespace App\Models\Configuracoes;

use Illuminate\Database\Eloquent\Model;

class DbConfig extends Model
{
    public $timestamps = false;

    protected $table = 'configuracoes.db_config';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $fillable = [
        'codigo',
        'nomeinst',
        'ender',
        'munic',
        'uf',
        'telef',
        'email',
        'ident',
        'tx_banc',
        'numbanco',
        'url',
        'logo',
        'figura',
        'dtcont',
        'diario',
        'pref',
        'vicepref',
        'fax',
        'cgc',
        'cep',
        'tpropri',
        'tsocios',
        'prefeitura',
        'bairro',
        'numcgm',
        'codtrib',
        'tribinst',
        'segmento',
        'formvencfebraban',
        'numero',
        'nomedebconta',
        'db21_tipoinstit',
        'db21_ativo',
        'db21_regracgmiss',
        'db21_regracgmiptu',
        'db21_codcli',
        'nomeinstabrev',
        'db21_usasisagua',
        'db21_codigomunicipoestado',
        'db21_datalimite',
        'db21_criacao',
        'db21_compl',
        'db21_imgmarcadagua',
        'db21_esfera',
        'db21_tipopoder',
        'db21_codtj',
        'db21_habitantes',
        'db21_usadistritounidade',
        'orderdepart',
        'db21_usadebitoitbi',
        'db21_honorarioadvocaticio',
        'db21_apirfb'
    ];
}
