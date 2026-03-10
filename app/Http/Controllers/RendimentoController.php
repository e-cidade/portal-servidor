<?php

namespace App\Http\Controllers;

use App\Repositories\RhPessoalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Mpdf\Mpdf;

class RendimentoController extends Controller
{
    protected $rhPessoalRepository;

    public function __construct(RhPessoalRepository $rhPessoalRepository)
    {
        $this->rhPessoalRepository = $rhPessoalRepository;
    }

    public function index()
    {
        $numCgm = Auth::user()->usuacgm->cgmlogin;
        $anos = $this->rhPessoalRepository->getAnosRendimento($numCgm, CODIGOINSTITUICAO);

        return Inertia::render('Rendimentos', [
            'anos' => $anos,
        ]);
    }

    public function gerarPDF(Request $request)
    {
        $request->validate([
            'ano' => 'required|integer',
        ]);

        $numCgm = Auth::user()->usuacgm->cgmlogin;
        $anoBase = $request->ano;
        $instituicao = CODIGOINSTITUICAO;

        $rendimento = $this->rhPessoalRepository->getRendimentos($numCgm, $anoBase, $instituicao);

        if (!$rendimento) {
            return back()->with('error', 'Rendimento não encontrado para o ano selecionado.');
        }

        // Dados da instituição (prefeitura)
        $dadosInstituicao = DB::table('configuracoes.db_config')
            ->where('codigo', $instituicao)
            ->join('cgm', 'db_config.numcgm', '=', 'cgm.z01_numcgm')
            ->first();

        $n13Salario = ($rendimento->rendimento_13
            - $rendimento->prev_oficial_13
            - $rendimento->prev_privada_13
            - $rendimento->depend_13
            - $rendimento->aposentadoria_65_13
            - $rendimento->pensao_13
            - $rendimento->irrf_13);

        if ($n13Salario < 0) {
            $n13Salario = 0;
        }

        $dados = [
            'instituicao' => $dadosInstituicao,
            'rendimento'  => $rendimento,
            'anoBase'     => $anoBase,
            'n13Salario'  => $n13Salario,
        ];

        $html = view('rendimentos_pdf', $dados)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="rendimento_'.$anoBase.'.pdf"');
    }
}
