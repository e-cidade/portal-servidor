@extends('layouts.app')

@section('content')
    <div class="bg-white p-8 rounded-xl shadow-lg text-center">
        <div class="flex justify-center mb-4">
            <div class="bg-green-100 p-4 rounded-full">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-green-700">Documento Autenticado</h1>
        <p class="mt-3 text-gray-700">Este contracheque é válido e foi autenticado no sistema.</p>

        <div class="mt-6 text-left text-sm text-gray-600 border-t pt-4 space-y-1">
            <p><span class="font-semibold">Registro:</span> {{ $contracheque->rh85_regist }}</p>
            <p><span class="font-semibold">Mês/Ano:</span> {{ str_pad($contracheque->rh85_mesusu, 2, '0', STR_PAD_LEFT) }}/{{ $contracheque->rh85_anousu }}</p>
            <p><span class="font-semibold">Emitido em:</span> {{ \Carbon\Carbon::parse($contracheque->rh85_dataemissao)->format('d/m/Y') }} às {{ $contracheque->rh85_horaemissao }}</p>
            <p><span class="font-semibold">Código de Autenticação:</span> {{ $contracheque->rh85_codautent }}</p>
        </div>
    </div>
@endsection
