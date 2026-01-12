@extends('layouts.app')
@php
  // Helpers locais
  $moeda = fn($v) => number_format((float)($v ?? 0), 2, ',', '.');
  $qtd   = fn($v) => is_null($v) ? '' : number_format((float)$v, 2, ',', '.');

  // Totais vindos do backend (se não vierem, inicializa)
  $totais = $totais ?? [
    'base_prev'         => 0,
    'base_irrf'         => 0,
    'tot_desc'          => 0,
    'tot_prov'          => 0,
    'saldo_devedor'     => 0,
    'base_irrf_ferias'  => 0,
  ];
  $liquido = (float)$totais['tot_prov'] - (float)$totais['tot_desc'];
@endphp

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
        <h3 class="text-2xl font-bold text-green-700">Resumo</h3>
        <div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 8px;">
                         Proventos
                    </th>
                    <th style="border: 1px solid #ccc; padding: 8px;">
                         Descontos
                    </th>
                    <th style="border: 1px solid #ccc; padding: 8px;">
                        Líquido a Receber
                    </th>
                </tr>
                </thead>

                <tbody>
                <tr style="background-color: #ffffff;">
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">
                        {{ $moeda($totais['tot_prov']) }}
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">
                        {{ $moeda($totais['tot_desc']) }}
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: right; font-weight: bold;">
                        {{ $moeda($liquido) }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>


        <div class="mt-6 text-left text-sm text-gray-600 border-t pt-4 space-y-1">
            <p><span class="font-semibold">Registro:</span> {{ $contracheque->rh85_regist }}</p>
            <p><span class="font-semibold">Mês/Ano:</span> {{ str_pad($contracheque->rh85_mesusu, 2, '0', STR_PAD_LEFT) }}/{{ $contracheque->rh85_anousu }}</p>
            <p><span class="font-semibold">Emitido em:</span> {{ \Carbon\Carbon::parse($contracheque->rh85_dataemissao)->format('d/m/Y') }} às {{ $contracheque->rh85_horaemissao }}</p>
            <p><span class="font-semibold">Código de Autenticação:</span> {{ $contracheque->rh85_codautent }}</p>
        </div>
    </div>
@endsection
