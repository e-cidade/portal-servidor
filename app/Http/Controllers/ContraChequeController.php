<?php

namespace App\Http\Controllers;

use App\Models\Configuracoes\DbConfig;
use App\Models\RhemiteContracheque;
use App\Services\ContrachequeService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Inertia\Inertia;
use Inertia\Response;
use App\Repositories\RhPessoalRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;

class ContraChequeController extends Controller
{

    protected $rhPessoalRepository;
    protected $service;
    protected $tiposFolha;

    public function __construct(RhPessoalRepository $rhPessoalRepository, ContrachequeService $service)
    {
        $this->rhPessoalRepository = $rhPessoalRepository;
        $this->service = $service;
        $this->tiposFolha = [
            'r14'  => 'SALÁRIO',
            'r22'  => 'ADICIONAL',
            'r35'  => '13 SALARIO',
            'r20'  => 'RECISÃO',
            'r48'  => 'COMPLEMENTAR',
            'supl' => null,
        ];
    }

    public function index(): Response
    {
        $numCgm = Auth::user()->usuacgm->cgmlogin;
        $matriculas = $this->rhPessoalRepository->getMatriculasAtivas($numCgm, CODIGOINSTITUICAO);
// dd($matriculas);
        return Inertia::render('Contracheque', [
            'matriculas' => $matriculas,
        ]);
    }


    public function consulta(Request $request): Response
    {

        $numCgm = Auth::user()->usuacgm->cgmlogin;
        $matriculas = $this->rhPessoalRepository->getMatriculasAtivas($numCgm, CODIGOINSTITUICAO);
        $rubricas = $this->rhPessoalRepository->getRubricas($request->tipo_folha, $request->ano, $request->mes, $request->registro);
        $tiposFolha = $this->rhPessoalRepository->getTipoFolha($request->registro, $request->ano, $request->mes);
        $totais     = $rubricas['resumo'] ?? (object) [
            'base_prev' => 0,
            'base_irrf' => 0,
            'tot_desc' => 0,
            'tot_prov' => 0,
            'saldo_devedor' => 0,
            'base_irrf_ferias' => 0,
        ];
        return Inertia::render('Contracheque', [
            'matriculas' => $matriculas,
            'contra_cheque' => $rubricas,
            'tipos_folha' => $tiposFolha,
            'totais' => $totais,
            'filters' => [
                            'registro' => (int) $request->registro,
                            'ano' => (int) $request->ano,
                            'mes' => (int) $request->mes,
                            'tipo_folha' => (int) $request->tipo_folha,
                        ],
        ]);
    }


    public function tipoFolha(Request $request): Response
    {

        $numCgm = Auth::user()->usuacgm->cgmlogin;
        $matriculas = $this->rhPessoalRepository->getMatriculasAtivas($numCgm, CODIGOINSTITUICAO);
        $tiposFolha = $this->rhPessoalRepository->getTipoFolha($request->registro, $request->ano, $request->mes);
        return Inertia::render('Contracheque', [
            'matriculas' => $matriculas,
            'tipos_folha' => $tiposFolha,
        ]);
    }

    public function gerarPDFContraCheque(Request $request)
    {

        $contracheque = $this->service->gerarContracheque($request->registro, $request->ano, $request->mes, $request->tipo_folha);

        $dados = $this->rhPessoalRepository->getRubricas($request->tipo_folha, $request->ano, $request->mes, $request->registro) ?? collect();

        $servidor = $this->rhPessoalRepository->getDadosServidor( $request->ano, $request->mes, $request->registro, CODIGOINSTITUICAO);

        $paramFerias = $this->rhPessoalRepository->getParametrosFerias($request->ano, $request->mes, CODIGOINSTITUICAO);

        $ferias = collect();

        if($paramFerias) {

            $periodoFerias = $this->rhPessoalRepository->getFeriasCompetencia($request->registro, $request->ano, $request->mes);

            if ($periodoFerias->isNotEmpty()) {
                $ferias = $periodoFerias->map(function ($feria) {
                    return sprintf(
                        '%s à %s',
                        \Carbon\Carbon::parse($feria->data_inicio)->format('d/m/Y'),
                        \Carbon\Carbon::parse($feria->data_fim)->format('d/m/Y')
                    );
                });
            }
        }
                
        $empresa = (object)[
            'nome' => $servidor->nomeinst,
            'cnpj' => $servidor->cgc,
            'endereco' => $servidor->ender,
            'uf' => $servidor->uf,
            'munic' => $servidor->munic,
            'bairro' => $servidor->bairro,
            'numero' => $servidor->numero,
        ];

        $colaborador = (object)[
            'nome' => $servidor->z01_nome,
            'matricula' => $request->registro,
            'cpf' => $servidor->z01_cgccpf,
            'cargo' => $servidor->rh37_descr,
            'vinculo' => $servidor->rh30_descr,
            'lotacao' => $servidor->r70_descr,
            'pis' => $servidor->rh16_pis,
            'banco' => $servidor->rh44_codban,
            'agencia' => $servidor->rh44_agencia,
            'dvagencia' => $servidor->rh44_dvagencia,
            'conta' => $servidor->rh44_conta,
            'dvconta' => $servidor->rh44_dvconta,
            'admiss'=> $servidor->rh01_admiss,
            'ferias' => $ferias,
            'paramFerias' => $paramFerias,
        ];

        $competencia = (object)['mes' => $request->mes, 'ano' => $request->ano];

        $rubricas = $dados['rubricas'] ?? collect();

        $totais     = $dados['resumo'] ?? (object) [
            'base_prev' => 0,
            'base_irrf' => 0,
            'tot_desc' => 0,
            'tot_prov' => 0,
            'saldo_devedor' => 0,
            'base_irrf_ferias' => 0,
        ];

        $tipoFolha = $this->tiposFolha[$request->tipo_folha] ?? null;

        $urlValidacao = route('contracheque.validar', ['token' => $contracheque->rh85_codautent]);

        $qrcode = base64_encode(
            QrCode::format('png')
                ->size(120)
                ->margin(2)
                ->generate($urlValidacao)
        );

        $html = view('contracheque', compact('empresa', 'colaborador', 'competencia', 'rubricas', 'totais', 'tipoFolha', 'qrcode', 'urlValidacao'))->render();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="contracheque.pdf"');

    }

    public function validar($token)
    {
        $contracheque = RhemiteContracheque::where('rh85_codautent', $token)->first();

        if($contracheque){
            $dados = $this->rhPessoalRepository->getRubricas($contracheque->rh85_sigla, $contracheque->rh85_anousu, $contracheque->rh85_mesusu, $contracheque->rh85_regist) ?? collect();
        }

        $totais     = $dados['resumo'] ?? (object) [
            'base_prev' => 0,
            'base_irrf' => 0,
            'tot_desc' => 0,
            'tot_prov' => 0,
            'saldo_devedor' => 0,
            'base_irrf_ferias' => 0,
        ];
        $instituicao = DbConfig::where('codigo', CODIGOINSTITUICAO)->select('codigo', 'nomeinst', 'ender', 'munic', 'uf', 'cep', 'bairro', 'numero', 'logo')->first();

        if (!$contracheque) {
            return view('validacao.invalido', compact('instituicao'));
        }

        return view('validacao.valido', [
            'contracheque' => $contracheque,
            'instituicao' => $instituicao,
            'totais' => $totais,
        ]);
    }



}
