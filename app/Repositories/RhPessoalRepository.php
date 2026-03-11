<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use function Laravel\Prompts\table;

class RhPessoalRepository
{
    /**
     * Busca informações completas de um registro específico.
     *
     * @param  int  $matricula
     * @return object|null
     */
    public function findByMatricula(int $ano, int $mes, int $matricula, int $intituicao)
    {
        $sql = "
            SELECT pessoal.rhpessoal.*, protocolo.cgm.*, pessoal.rhlota.*, pessoal.rhpesbanco.*, protocolo.db_cgmruas.*, pessoal.rhestcivil.*,
                    pessoal.rhfuncao.*, pessoal.rhpesdoc.*, pessoal.rhinstrucao.*,
                   rh16_pis,
                   SUBSTR(
                        (SELECT * FROM db_fxxx(rh02_regist, rh02_anousu, rh02_mesusu, rh02_instit)),
                        210, 25
                   ) AS padraoatual
            FROM pessoal.rhpessoal
            INNER JOIN pessoal.rhpessoalmov
                ON rh02_anousu = ?
               AND rh02_mesusu = ?
               AND rh02_regist = rh01_regist
            LEFT JOIN pessoal.rhlota
                ON pessoal.rhlota.r70_codigo = pessoal.rhpessoalmov.rh02_lota
               AND pessoal.rhlota.r70_instit = pessoal.rhpessoalmov.rh02_instit
            LEFT JOIN pessoal.rhpesbanco
                ON rh44_seqpes = pessoal.rhpessoalmov.rh02_seqpes
            INNER JOIN protocolo.cgm
                ON protocolo.cgm.z01_numcgm = pessoal.rhpessoal.rh01_numcgm
            LEFT JOIN protocolo.db_cgmruas
                ON protocolo.db_cgmruas.z01_numcgm = protocolo.cgm.z01_numcgm
            LEFT JOIN cadastro.ruas
                ON cadastro.ruas.j14_codigo = protocolo.db_cgmruas.j14_codigo
            LEFT JOIN cadastro.ruastipo
                ON cadastro.ruastipo.j88_codigo = cadastro.ruas.j14_tipo
            INNER JOIN pessoal.rhestcivil
                ON pessoal.rhestcivil.rh08_estciv = pessoal.rhpessoal.rh01_estciv
            LEFT JOIN pessoal.rhfuncao
                ON pessoal.rhfuncao.rh37_funcao = pessoal.rhpessoal.rh01_funcao
               AND pessoal.rhfuncao.rh37_instit = pessoal.rhpessoalmov.rh02_instit
            LEFT JOIN pessoal.rhregime
                ON pessoal.rhregime.rh30_codreg = pessoal.rhpessoalmov.rh02_codreg
               AND pessoal.rhregime.rh30_instit = pessoal.rhpessoalmov.rh02_instit
            INNER JOIN pessoal.rhinstrucao
                ON pessoal.rhinstrucao.rh21_instru = pessoal.rhpessoal.rh01_instru
            LEFT JOIN pessoal.rhpespadrao
                ON pessoal.rhpespadrao.rh03_seqpes = pessoal.rhpessoalmov.rh02_seqpes
            LEFT JOIN pessoal.rhpesrescisao
                ON rh02_seqpes = rh05_seqpes
            LEFT JOIN pessoal.rhpesdoc
                ON rh16_regist = rh01_regist
            WHERE pessoal.rhpessoal.rh01_regist in (?) and rh01_instit = ?
        ";
        return DB::selectOne($sql, [$ano, $mes, $matricula, $intituicao]);
    }


    /**
     * Busca informações completas de um registro específico.
     *
     * @param  int  $matricula
     * @return object|null
     */
    public function getMatriculaAtiva(int $numcgm, int $intituicao)
    {
        // Encontrar o maior ano
        $maiorAno = DB::table('pessoal.rhpessoalmov')->join('pessoal.rhpessoal', 'rh02_regist', '=', 'rh01_regist')
            ->where('rh01_instit', '=', $intituicao)
            ->where('rh01_numcgm', '=', $numcgm)
            ->max('rh02_anousu');

        // Encontrar o maior mês
        $maiorMes = DB::table('pessoal.rhpessoalmov')->join('pessoal.rhpessoal', 'rh02_regist', '=', 'rh01_regist')
            ->where('rh02_anousu', '=', $maiorAno) // Filtrar pelo maior ano para garantir que o mês seja do ano mais recente
            ->where('rh01_instit', '=', $intituicao)
            ->where('rh01_numcgm', '=', $numcgm)
            ->max('rh02_mesusu');

        return DB::table('pessoal.rhpessoal')
            ->join('pessoal.rhpessoalmov', 'rh02_regist', '=', 'rh01_regist')
            ->leftJoin('pessoal.rhpesrescisao', 'rh05_seqpes', '=', 'rh02_seqpes')
            ->select('rh01_regist', 'rh02_anousu', 'rh02_mesusu')
            ->where('rh01_instit', '=', $intituicao)
            ->where('rh02_anousu', '=', $maiorAno)
            ->where('rh02_mesusu', '=', $maiorMes)
            ->where('rh01_numcgm', '=', $numcgm)
            ->whereNull('rh05_recis')
            ->first();
    }

    /**
     * Busca informações completas de um registro específico.
     *
     * @param  int  $matricula
     * @return object|null
     */
    public function getMatriculasAtivas(int $numcgm, int $intituicao)
    {
         $sql = "select distinct rh02_regist as regist, rh02_anousu as anousu, rh02_mesusu as mesusu
                           from rhpessoalmov
                                inner join rhpessoal on rh02_regist=rh01_regist
                                left join rhpesrescisao on rh05_seqpes = rh02_seqpes
                                LEFT JOIN cfpess ON (rh02_anousu,rh02_mesusu) = (r11_anousu,r11_mesusu) AND ((r11_dtfechamento+r11_diasliberarcontracheque) <= current_date OR r11_dtfechamento IS NULL)
                          where rh01_numcgm  = {$numcgm}
                            and rh02_instit  = {$intituicao}
                            and case when rh02_anousu = fc_anofolha({$intituicao})
                                      and rh02_mesusu = fc_mesfolha({$intituicao}) then false else true end
                          order by rh02_mesusu desc";

          return DB::query()->from(DB::raw("($sql) as t"))->get();
    }


    public function getDependentes(int $iMatric, int $ano, int $mes, int $instit)
    {
        $sql = "select rhdepend.*, fc_tipo_dependente( ?, rh31_codigo::integer, ?, ?, ?)
                  from rhdepend
                 where rh31_regist = ? order by rh31_nome ";
        return DB::select($sql, [$iMatric, $ano, $mes, $instit, $iMatric]);
    }

    public function getFeriasGozadas(int $iMatric, int $ano, int $mes)
    {
        return DB::table('cadferia')
            ->where('r30_regist', $iMatric)
            ->where('r30_anousu', $ano)
            ->where('r30_mesusu', $mes)
            ->select('cadferia.*')
            ->paginate(5);
    }

    public function getFeriasAGozadas(int $iMatric, int $iAno, int $iMes)
    {
        $subqueryX = DB::table('rhpessoal')
            ->join('rhpessoalmov', function ($join) use ($iAno, $iMes) {
                $join->on('rh02_regist', '=', 'rh01_regist')
                    ->where('rh02_anousu', '=', $iAno)
                    ->where('rh02_mesusu', '=', $iMes);
            })
            ->leftJoin('rhpesrescisao', 'rh05_seqpes', '=', 'rh02_seqpes')
            ->leftJoin('cadferia', function ($join) {
                $join->on('r30_regist', '=', 'rh02_regist')
                    ->whereColumn('r30_anousu', 'rh02_anousu')
                    ->whereColumn('r30_mesusu', 'rh02_mesusu');
            })
            ->leftJoin('rhregime', 'rh30_codreg', '=', 'rh02_codreg')
            ->where('rh01_regist', '=', $iMatric)
            ->whereNull('rh05_seqpes')
            ->whereNotIn('rh30_vinculo', ['P', 'I'])
            ->select(DB::raw("CASE WHEN r30_peraf IS NOT NULL THEN r30_peraf ELSE rh01_admiss END AS ultimo_periodo"), 'rhpessoalmov.*', 'rhpessoal.*', 'cadferia.*', 'rhregime.*');

        $subqueryY = DB::query()
            ->fromSub($subqueryX, 'x')
            ->select(
                DB::raw("to_char(max(ultimo_periodo) + cast('1 day' as interval), 'YYYY-MM-DD') as periodo_aquisitivo_inicial"),
                DB::raw("to_char(max(ultimo_periodo) + cast('1 year' as interval), 'YYYY-MM-DD') as periodo_aquisitivo_final")
            );

        return DB::query()
            ->fromSub($subqueryY, 'y')
            ->whereNotNull('periodo_aquisitivo_inicial')
            ->whereNotNull('periodo_aquisitivo_final')
            ->get();
    }

    public function getTipoFolha($matric, $anousu, $mesusu)
    {
        $query1 = DB::table('gerfsal')
            ->selectRaw("'r14' as codigo, CASE WHEN r14_regist IS NOT NULL THEN 'Salário' END as descr")
            ->where('r14_regist', $matric)
            ->where('r14_anousu', $anousu)
            ->where('r14_mesusu', $mesusu)
            ->distinct();

        $query2 = DB::table('gerfadi')
            ->selectRaw("'r22' as codigo, CASE WHEN r22_regist IS NOT NULL THEN 'Adiantamento' END as descr")
            ->where('r22_regist', $matric)
            ->where('r22_anousu', $anousu)
            ->where('r22_mesusu', $mesusu)
            ->distinct();

        $query3 = DB::table('gerfcom')
            ->selectRaw("'r48' as codigo, CASE WHEN r48_regist IS NOT NULL THEN 'Complementar' END as descr")
            ->where('r48_regist', $matric)
            ->where('r48_anousu', $anousu)
            ->where('r48_mesusu', $mesusu)
            ->distinct();

        $query4 = DB::table('gerfs13')
            ->selectRaw("'r35' as codigo, CASE WHEN r35_regist IS NOT NULL THEN '13º Salário' END as descr")
            ->where('r35_regist', $matric)
            ->where('r35_anousu', $anousu)
            ->where('r35_mesusu', $mesusu)
            ->distinct();

        $query5 = DB::table('gerfres')
            ->selectRaw("'r20' as codigo, CASE WHEN r20_regist IS NOT NULL THEN 'Rescisão' END as descr")
            ->where('r20_regist', $matric)
            ->where('r20_anousu', $anousu)
            ->where('r20_mesusu', $mesusu)
            ->distinct();

        return $query1
            ->unionAll($query2)
            ->unionAll($query3)
            ->unionAll($query4)
            ->unionAll($query5)
            ->get();
    }


    /**
     * Retorna as rubricas do colaborador para o tipo de folha selecionado
     *
     * @param string $sigla     Prefixo da tabela (ex.: r14, r22, r35)
     * @param int    $ano       Ano de referência
     * @param int    $mes       Mês de referência
     * @param int    $matricula Matrícula do colaborador
     * @return array
     */
    function getRubricas(string $sigla, int $ano, int $mes, int $matricula)
    {
        // Define as tabelas de acordo com a sigla
        $tabelas = [
            'r14'  => 'gerfsal',
            'r22'  => 'gerfadi',
            'r35'  => 'gerfs13',
            'r20'  => 'gerfres',
            'r48'  => 'gerfcom',
            'supl' => null,
        ];

        $tabela = $tabelas[$sigla] ?? null;

        if (!$tabela) {
            throw new InvalidArgumentException("Sigla {$sigla} não suportada ou sem tabela definida.");
        }

        // Totais iniciados com 0
        $resumo = [
            'base_prev'       => 0,
            'base_irrf'       => 0,
            'tot_desc'        => 0,
            'tot_prov'        => 0,
            'saldo_devedor'   => 0,
            'base_irrf_ferias' => 0,
        ];

        // Monta a query
        $rubricas = DB::table('rhpessoal')
            ->selectRaw("
                {$sigla}_rubric as rubric,
                {$sigla}_quant  as quant,
                rh27_descr      as descr,
                {$sigla}_valor  as valor,
                {$sigla}_pd     as tipo,
                case when {$sigla}_pd = 1 then {$sigla}_valor else 0 end as provento,
                case when {$sigla}_pd = 2 then {$sigla}_valor else 0 end as desconto
            ")
            ->join('rhpessoalmov', function ($join) use ($mes, $ano) {
                $join->on('rh02_regist', '=', 'rh01_regist')
                    ->where('rh02_anousu', '=', $ano)
                    ->where('rh02_mesusu', '=', $mes)
                    ->where( 'rh02_instit', '=',CODIGOINSTITUICAO);
            })
            ->join($tabela, function ($join) use ($sigla, $ano, $mes) {
                $join->on("{$sigla}_regist", '=', 'rh01_regist')
                    ->where("{$sigla}_anousu", '=', $ano)
                    ->where("{$sigla}_mesusu", '=', $mes)
                    ->on("{$sigla}_instit", '=', 'rh02_instit');
            })
            ->join('rhrubricas', function ($join) use ($sigla) {
                $join->on('rh27_rubric', '=', "{$sigla}_rubric")
                    ->on('rh27_instit', '=', 'rh02_instit');
            })
            ->join('cfpess', function ($join) use ($mes, $ano) {
                $join->where('r11_instit', '=', CODIGOINSTITUICAO)
                    ->where('r11_anousu', '=', $ano)
                    ->where('r11_mesusu', '=', $mes)
                    ->whereNotNull('r11_dtfechamento');
            })

            ->where('rh01_regist', $matricula)
            ->orderBy("{$sigla}_pd")
            ->orderBy("{$sigla}_rubric")
            ->get();

        // Calcula os totais de acordo com as regras
        foreach ($rubricas as $r) {
            if ($r->rubric === 'R981' || $r->rubric === 'R982') {
                $resumo['base_irrf'] += $r->valor;
            } elseif ($r->rubric === 'R992') {
                $resumo['base_prev'] += $r->valor;
            } elseif ($r->rubric === 'R983') {
                $resumo['base_irrf_ferias'] += $r->valor;
            } elseif ($r->rubric === 'R928') {
                $resumo['saldo_devedor'] += $r->valor;
            }

            // Soma total de proventos e descontos
            $resumo['tot_prov']  += $r->provento;
            $resumo['tot_desc']  += $r->desconto;
        }

        $rubricas_filtradas = array_filter($rubricas->toArray(), function ($rubrica) {
            return $rubrica->tipo !== 3;
        });

        // Retorna rubricas + resumo calculados
        return [
            'rubricas' => $rubricas_filtradas,
            'resumo'   => $resumo,
        ];
    }

    public function getDadosServidor($ano, $mes, $matricula, $instituicao)
    {
        return DB::table('configuracoes.db_config')
            ->select('db_config.*', 'rh02_instit as institservidor', 'cgm.*' , 'r70_descr', 'rh30_descr', 'rh37_descr' , 'rh16_pis' , 'rhpesbanco.*', 'rh01_admiss')
            ->join('rhpessoalmov', function($join) use ($ano, $mes, $instituicao) {
                $join->on('rh02_instit', '=', 'codigo')
                    ->where('rh02_instit', '=', $instituicao)
                    ->where('rh02_anousu', '=', $ano)
                    ->where('rh02_mesusu', '=', $mes);
            })
            ->join('rhlota', 'rh02_lota', '=', 'rhlota.r70_codigo')
            ->join('rhregime', 'rh30_codreg', '=', 'rh02_codreg')
            ->join('rhpessoal', 'rh02_regist', '=', 'rh01_regist')
            ->join('rhfuncao', function($join) use ($ano, $mes, $instituicao) {
                $join->on('rh37_funcao', '=', 'rh02_funcao')
                    ->where('rh37_instit', '=', $instituicao);
            })
            ->join('cgm', 'rh01_numcgm', '=', 'z01_numcgm')
            ->leftJoin('rhpesbanco'     , 'rh44_seqpes', '=' ,'rh02_seqpes')
            ->leftJoin('rhpesdoc',  'rh16_regist', '=','rh01_regist')
            ->where('rh02_regist', $matricula)->first();

    }

    public function getRendimentos(int $numCgm, int $anoBase, int $instituicao)
    {
        $rendimentos = DB::table('rhdirfgeracaodadospessoal as rhdirfgeracaodadospessoal_outer')
            ->join('pessoal.rhdirfgeracao as g', 'g.rh95_sequencial', '=', 'rhdirfgeracaodadospessoal_outer.rh96_rhdirfgeracao')
            ->join('cgm as c', 'c.z01_numcgm', '=', 'rhdirfgeracaodadospessoal_outer.rh96_numcgm')
            ->select(
                'rhdirfgeracaodadospessoal_outer.rh96_sequencial',
                'rhdirfgeracaodadospessoal_outer.rh96_numcgm',
                'c.z01_nome',
                'rhdirfgeracaodadospessoal_outer.rh96_cpfcnpj',
                DB::raw("
            (
                select array_agg(distinct r.rh99_regist order by r.rh99_regist asc)
                from rhdirfgeracaodadospessoalvalor v
                inner join rhdirfgeracaopessoalregist r
                    on r.rh99_rhdirfgeracaodadospessoalvalor = v.rh98_sequencial
                where v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial
            ) as regist
        "),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 1 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as rendimento"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 1 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as rendimento_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 2 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as prev_oficial"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 2 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as prev_oficial_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 3 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as prev_privada"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 3 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as prev_privada_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 4 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as depend"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 4 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as depend_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 5 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as pensao"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 5 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as pensao_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 6 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as irrf"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 6 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as irrf_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 7 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as aposentadoria_65"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 7 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as aposentadoria_65_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 8 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as diaria"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 9 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as ind_rescisao"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 10 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as abono"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 15 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as outros5"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 11 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as molestia_grave_inativos"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 11 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as molestia_grave_inativos_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 12 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as molestia_grave_ativos"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor = 12 and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes = 13), 0) as molestia_grave_ativos_13"),
                DB::raw("coalesce((select sum(v.rh98_valor) from rhdirfgeracaodadospessoalvalor v where v.rh98_rhdirftipovalor in (13,14) and v.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal_outer.rh96_sequencial and v.rh98_mes between 1 and 12), 0) as plano_saude")
            )
            ->where('g.rh95_ano', $anoBase)
            ->where('rhdirfgeracaodadospessoal_outer.rh96_numcgm', $numCgm)
            ->whereExists(function ($q) use ($instituicao) {
                $q->select(DB::raw(1))
                    ->from('rhdirfgeracaodadospessoalvalor as v')
                    ->whereColumn('v.rh98_rhdirfgeracaodadospessoal', 'rhdirfgeracaodadospessoal_outer.rh96_sequencial')
                    ->where('v.rh98_instit', $instituicao);
            })
            ->first();
        if ($rendimentos && $rendimentos->pensao > 0) {
            $registros = str_replace(['{', '}'], '', $rendimentos->regist);
            $pensionistas = DB::table('pensao')
                ->join('cgm', 'r52_numcgm', '=', 'z01_numcgm')
                ->whereIn('r52_regist', explode(',', $registros))
                ->distinct()
                ->pluck('z01_nome')
                ->toArray();
            $rendimentos->pensionistas = implode(', ', $pensionistas);
        } else if ($rendimentos) {
            $rendimentos->pensionistas = '';
        }

        return $rendimentos;
    }

    public function getAnosRendimento(int $numCgm, int $instituicao)
    {
        $cpf = DB::table('cgm')->where('z01_numcgm', $numCgm)->first();
        $cgms = DB::table('cgm')->where('z01_cgccpf', $cpf->z01_cgccpf)->select('z01_numcgm')->get();
        return DB::table('rhdirfgeracao')
            ->join('rhdirfgeracaodadospessoal', 'rh95_sequencial', '=', 'rh96_rhdirfgeracao')
            ->join('rhdirfgeracaodadospessoalvalor', 'rh96_sequencial', '=', 'rh98_rhdirfgeracaodadospessoal')
            ->whereIn('rh96_numcgm', $cgms->pluck('z01_numcgm')->toArray())
            ->where('rh98_instit', $instituicao)
            ->select('rh95_ano')
            ->distinct()
            ->orderBy('rh95_ano', 'desc')
            ->pluck('rh95_ano');
    }
}
