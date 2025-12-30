<?php


namespace App\Services;

use App\Models\RhemiteContracheque;
use Illuminate\Support\Facades\DB;

class ContrachequeService
{
    /**
     * Gera um contracheque com código de autenticação único.
     * Remove o anterior do mesmo registro/ano/mês, se existir.
     */
    public function gerarContracheque(int $regist, int $ano, int $mes, string $sigla = 'r14'): RhemiteContracheque
    {
        return DB::transaction(function () use ($regist, $ano, $mes, $sigla) {

            // 1. Apaga contracheque antigo do mesmo funcionário/ano/mês
            RhemiteContracheque::where('rh85_regist', $regist)
                ->where('rh85_anousu', $ano)
                ->where('rh85_mesusu', $mes)
                ->delete();

            // 2. Cria novo contracheque
            $contracheque = new RhemiteContracheque();
            $contracheque->rh85_regist      = $regist;
            $contracheque->rh85_anousu      = $ano;
            $contracheque->rh85_mesusu      = $mes;
            $contracheque->rh85_sigla       = substr($sigla, 0, 3);
            $contracheque->rh85_dataemissao = now()->format('Y-m-d');
            $contracheque->rh85_horaemissao = now()->format('H:i');
            $contracheque->rh85_ip          = request()->ip();
            $contracheque->rh85_externo     = false;

            // Salva para gerar rh85_sequencial automaticamente pela sequence
            $contracheque->save();

            // 3. Monta o código de autenticação (roda de autenticidade)
            $contracheque->rh85_codautent =
                str_pad($regist, 7, '0', STR_PAD_LEFT)   // registro
                . str_pad($mes, 2, '0', STR_PAD_LEFT)      // mês
                . $ano                                    // ano
                . str_pad($contracheque->rh85_sequencial, 6, '0', STR_PAD_LEFT);

            // Atualiza com o código de autenticidade
            $contracheque->save();

            return $contracheque;
        });
    }
}

