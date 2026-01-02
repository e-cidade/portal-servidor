<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use App\Repositories\RhPessoalRepository;
use Illuminate\Support\Facades\Auth;

class DependentesController extends Controller
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
        $dependentes = (object) [];
        if($matricula){
            $dependentes = $this->rhPessoalRepository->getDependentes($matricula->rh01_regist, $matricula->rh02_anousu, $matricula->rh02_mesusu, CODIGOINSTITUICAO) ?? (object) [];
        }
        return Inertia::render('Dependentes', [
            'dependentes' => $dependentes ? $dependentes : [],
        ]);
    }

}
