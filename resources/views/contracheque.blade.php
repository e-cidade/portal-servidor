<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Recibo - {{ $competencia->mes ?? '' }}/{{ $competencia->ano ?? '' }}</title>
  <style>

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        color: #222;
    }

    h1, h2, h3 {
        margin: 0; padding: 0;
    }

    .muted {
        color: #666;
    }

    .row {
        width: 100%;
    }

    .col-6 {
        width: 49%;
        display: inline-block;
        vertical-align: top;
    }

    .mt-8 {
        margin-top: 8px;
    }


    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    th, td {
        border: 0.5pt solid #999;
    }

    th {
        background: #eee;
        font-weight: bold;
        text-align: center;
    }

    td.num {
        text-align: right;
        white-space: nowrap;
    }

    td.center {
        text-align: center;
    }

    .no-border td {
        border: none;
    }

    .small {
        font-size: 9pt;
    }

    .box {
        border: 0.6pt solid #999;
        padding: 8px;
    }

    .box-top {
        border: 0.6pt solid #999;
        padding: 4px;
        margin-bottom: 12px;
    }

    .totais td {
        font-weight: bold;
        background: #f7f7f7;
    }

    .dados-colaborador {
        width: 100%;

        margin-bottom: 10px;
    }

    .linha {
        display: flex;
        /* Deixa 1% para margens */
        vertical-align: top; /* Alinha no topo */
    }

    .col {
        flex: 1;
        padding: 2px;
    }

    .col-20 {
        grid-column: span 1;
        width: 100%;
    }

    .col-30 {
        grid-column: span 1;
        width: 100%;
    }

    .b {
        font-weight: bold;
    }


  </style>
</head>

<body>

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

  // Segurança: coleção de rubricas
  $rubricas = collect($rubricas ?? []);
@endphp


<div class="" style="" >
    <table class="no-border" style="width: 100%; border-bottom: 0.5pt solid #999; margin-bottom: 10px;">
        <tr>
            <td style="width: 65%; height: 40px;">
                <div class="b" style="font-size: 12px;"> {{ $empresa->nome ?? '' }} </div>
                <div class="small muted" style="font-size: 8px;">
                    CNPJ: {{ \App\Facades\Format::cnpj($empresa->cnpj) ?? '—' }}<br>
                    {{ $empresa->endereco ?? '' }}, Número:  {{  $empresa->numero ?? '' }} Bairro: {{ $empresa->bairro  ?? '' }} <br>
                    Cidade: {{  $empresa->munic  ?? '' }} - {{  $empresa->uf ?? '' }} CEP: {{  $empresa->cep ?? '' }}
                </div>
            </td>
            <td style="width: 35%; text-align: right; height: 40px;">
                <div class="b" style="font-size: 12px;">RECIBO DE PAGAMENTO</div>
                <div class="small muted" style="font-size: 8px;">Competência: {{ $competencia->mes ?? '—' }}/{{ $competencia->ano ?? '—' }}</div>
                <div class="small muted" style="font-size: 8px;">Tipo Folha: {{ $tipoFolha ?? '' }}</div>
                <div class="small muted" style="font-size: 8px;">Emissão: {{ $emissao ?? now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>
</div>
{{-- ===== Dados do Colaborador ===== --}}
<div class="no-border " style="margin-top: 10px; font-size: 8px;">
    <table class="no-border" style="font-size: 8px; ">
        <tr>
            <td style=" flex: 1; padding: 0;" colspan="1"><span class="b">MATRÍCULA:</span> {{ $colaborador->matricula ?? ($matricula ?? '—') }}</td>
            <td style=" flex: 1; padding: 0; " colspan="3"><span class="b">NOME:</span> {{ $colaborador->nome ?? ($nome ?? '—') }}</td>
            <td style=" flex: 1; padding: 0; " colspan="3"><span class="b">CARGO: </span> {{ $colaborador->cargo ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="1"><span class="b">CPF:</span> {{ \App\Facades\Format::cpf($colaborador->cpf) ?? ($cpf ?? '—') }}</td>
            <td colspan="3"><span class="b">LOTAÇÃO: </span> {{ $colaborador->lotacao ?? '—' }}</td>
            <td colspan="3"><span class="b">VÍNCULO: </span> {{ $colaborador->vinculo ?? ($vinculo ?? '—') }}</td>
        </tr>
        <tr>
            <td colspan="3"><span class="b">BCO/AG/CONTA): </span>{{ $colaborador->banco . ' / ' .  $colaborador->agencia .'-'. $colaborador->dvagencia . ' / ' .  $colaborador->conta . '-' .  $colaborador->dvconta }}</td>
            <td><span class="b">ADMISSÃO: </span>{{ \Carbon\Carbon::parse($colaborador->admiss)->format('d/m/Y') }}</td>
        </tr>
    </table>
</div>

{{-- ===== Proventos/Descontos ===== --}}
<div class="" style="margin-top: 10px;">
<table class="">
  <thead>
    <tr>
      <th style="width: 12%;">Cód.</th>
      <th>Descrição</th>
      <th style="width: 12%;" class="center">Qtd.</th>
      <th style="width: 18%;" class="num">Proventos (R$)</th>
      <th style="width: 18%;" class="num">Descontos (R$)</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rubricas as $r)
      <tr>
        <td class="center">{{ $r->rubric ?? '' }}</td>
        <td>{{ $r->descr ?? '' }}</td>
        <td class="center">{{ $qtd($r->quant ?? null) }}</td>
        <td class="num">{{ $moeda($r->provento ?? 0) }}</td>
        <td class="num">{{ $moeda($r->desconto ?? 0) }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="5" class="center muted">Nenhuma rubrica encontrada para o período.</td>
      </tr>
    @endforelse

    {{-- Totais --}}
    <tr class="totais">
      <td colspan="3" class="num">Totais</td>
      <td class="num">{{ $moeda($totais['tot_prov']) }}</td>
      <td class="num">{{ $moeda($totais['tot_desc']) }}</td>
    </tr>
  </tbody>
</table>

</div>
{{-- ===== Resumo Financeiro ===== --}}
<div class="row mt-8 ">
  <div class="col-6" style="padding-bottom: 12px;">
    <table>
      <tbody>
        <tr>
          <th style="width: 65%;">Base INSS</th>
          <td class="num">{{ $moeda($totais['base_prev']) }}</td>
        </tr>
        <tr>
          <th>Base IRRF</th>
          <td class="num">{{ $moeda($totais['base_irrf']) }}</td>
        </tr>
        <tr>
          <th>Base IRRF (Férias)</th>
          <td class="num">{{ $moeda($totais['base_irrf_ferias']) }}</td>
        </tr>
        <tr>
          <th>Saldo Devedor</th>
          <td class="num">{{ $moeda($totais['saldo_devedor']) }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="col-6 ">
    <table>
      <tbody>
        <tr>
          <th style="width: 65%;">Total de Proventos</th>
          <td class="num">{{ $moeda($totais['tot_prov']) }}</td>
        </tr>
        <tr>
          <th>Total de Descontos</th>
          <td class="num">{{ $moeda($totais['tot_desc']) }}</td>
        </tr>
        <tr>
          <th class="b">Líquido a Receber</th>
          <td class="num b">{{ $moeda($liquido) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div style="margin-top: 120px; text-align: center;">
    <div style="font-size: 8px; margin-bottom: 5px;">
        Escaneie o QR Code para validar este documento:
    </div>
    <img src="data:image/png;base64,{{ $qrcode }}" alt="QR Code">
    <div style="font-size: 7px; margin-top: 5px; color: #555;">
        ou acesse: {{ $urlValidacao }}
    </div>
</div>

</body>
</html>
