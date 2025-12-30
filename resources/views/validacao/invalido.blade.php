@extends('layouts.app')

@section('content')
    <div class="bg-white p-8 rounded-xl shadow-lg text-center">
        <div class="flex justify-center mb-4">
            <div class="bg-red-100 p-4 rounded-full">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-red-700">Documento Não Encontrado</h1>
        <p class="mt-3 text-gray-700">O código informado não corresponde a nenhum contracheque válido.</p>

        <div class="mt-6 text-sm text-gray-600">
            <p>Verifique se o QR Code ou o código de autenticação foram digitados corretamente.</p>
        </div>
    </div>
@endsection
