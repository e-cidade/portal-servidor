<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Repositories\RhPessoalRepository;
use Illuminate\Support\Facades\Auth;

class FeriasController extends Controller
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
        $feriasAGozar = (object) [];
        $feriasGozadas = (object) [];
        if($matricula){
            $feriasGozadas = $this->rhPessoalRepository->getFeriasGozadas($matricula->rh01_regist, $matricula->rh02_anousu, $matricula->rh02_mesusu) ?? (object) [];
            $feriasAGozar = $this->rhPessoalRepository->getFeriasAGozadas($matricula->rh01_regist, $matricula->rh02_anousu, $matricula->rh02_mesusu) ?? (object) [];
        }
        return Inertia::render('Ferias', [
            'feriasGozadas' => $feriasGozadas,
            'feriasAGozar' => $feriasAGozar ? [$feriasAGozar] : [],
        ]);
    }

}
