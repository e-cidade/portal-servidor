<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Repositories\RhPessoalRepository;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    protected $rhPessoalRepository;

    public function __construct(RhPessoalRepository $rhPessoalRepository) {
        $this->rhPessoalRepository = $rhPessoalRepository;
    }
    /**
     * Show the password reset page.
     */
    public function index(): Response
    {

        $numCgm = Auth::user()->usuacgm->cgmlogin;
        $matricula = $this->rhPessoalRepository->getMatriculaAtiva($numCgm, CODIGOINSTITUICAO);
        $dadosServidor = (object) [];
        if($matricula){
            $dadosServidor = $this->rhPessoalRepository->findByMatricula($matricula->rh02_anousu, $matricula->rh02_mesusu, $matricula->rh01_regist, CODIGOINSTITUICAO) ?? (object) [];
        }

        return Inertia::render('Dashboard', [
            'dados_servidor' => $dadosServidor,
        ]);
    }

}
