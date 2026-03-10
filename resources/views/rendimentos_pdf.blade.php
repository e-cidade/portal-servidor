<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 3px;
        }
        .header {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .section-title {
            background-color: #e6e6e6;
            font-weight: bold;
            padding: 5px;
            border: 1px solid #000;
        }
        .label {
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .no-border-top {
            border-top: none !important;
        }
    </style>
</head>
<body>
    <table class="table-top">
        <tr class="no-border-top">
            <td colspan="2" class="" style="width: 70%;">
                <h3>MINISTÉRIO DA ECONOMIA</h3>
                <h3>SECRETARIA DA RECEITA FEDERAL DO BRASIL</h3>
                <h3>IMPOSTO SOBRE A RENDA DA PESSOA FÍSICA</h3>
            </td>
            <td colspan="2" class="">
                <h5>COMPROVANTE DE RENDIMENTOS PAGOS E DE RETENÇÃO DE IMPOSTO DE RENDA NA FONTE </h5>
                <h6>EXERCÍCIO: {{ $anoBase+1 }}</h6>
                <h6>ANO CALENDÁRIO {{ $anoBase }}</h6>
            </td>
        </tr>
    </table>
    <div>
        <p>Verifique as condições e o prazo para a apresentação da Declaração do Imposto sobre a Renda da Pessoa Física para este ano - calendário no site
            da Secretaria Especial da Receita Federal do Brasil na Internet, no endereço <https://www.gov.br/receitafederal/pt-br></p>
    </div>
    <table class="table">
        <tr>
            <td width="50%">
                <span class="label">1. FONTE PAGADORA - PESSOA JURÍDICA</span><br>
                CGC: {{ $instituicao->z01_cgccpf }}<br>
                NOME: {{ $instituicao->nomeinst }}
            </td>
            <td>
                <span class="label">2. PESSOA FÍSICA BENEFICIÁRIA DOS RENDIMENTOS</span><br>
                CPF: {{ $rendimento->rh96_cpfcnpj }}<br>
                NOME: {{ $rendimento->z01_nome }}
            </td>
        </tr>
    </table>

    <div class="section-title">3. RENDIMENTOS TRIBUTÁVEIS, DEDUÇÕES E IMPOSTO RETIDO NA FONTE</div>
    <table class="table">
        <tr>
            <td width="80%">01. Total dos Rendimentos (inclusive férias)</td>
            <td class="text-right">{{ number_format($rendimento->rendimento, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>02. Contribuição Previdenciária Oficial</td>
            <td class="text-right">{{ number_format($rendimento->prev_oficial, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>03. Contribuições a Entidades de Previdência Privada</td>
            <td class="text-right">{{ number_format($rendimento->prev_privada, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>04. Pensão Alimentícia</td>
            <td class="text-right">{{ number_format($rendimento->pensao + $rendimento->pensao_13, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>05. Imposto de Renda Retido na Fonte</td>
            <td class="text-right">{{ number_format($rendimento->irrf, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="section-title">4. RENDIMENTOS ISENTOS E NÃO TRIBUTÁVEIS</div>
    <table class="table">
        <tr>
            <td width="80%">01. Parcela Isenta de Aposentadoria, Reserva, Reforma e Pensão (65 anos ou mais)</td>
            <td class="text-right">{{ number_format($rendimento->aposentadoria_65 + $rendimento->aposentadoria_65_13, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>02. Diárias e Ajudas de Custo</td>
            <td class="text-right">{{ number_format($rendimento->diaria, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>03. Proventos de Aposentadoria por Moléstia Grave ou Acidente em Serviço</td>
            <td class="text-right">{{ number_format($rendimento->molestia_grave_inativos + $rendimento->molestia_grave_inativos_13 + $rendimento->molestia_grave_ativos + $rendimento->molestia_grave_ativos_13, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>04. Indenizações por Rescisão de Contrato de Trabalho e PDV</td>
            <td class="text-right">{{ number_format($rendimento->ind_rescisao, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>05. Abono Pecuniário</td>
            <td class="text-right">{{ number_format($rendimento->abono, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>06. Outros (especificar):</td>
            <td class="text-right">{{ number_format($rendimento->outros5, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="section-title">5. RENDIMENTOS SUJEITOS A TRIBUTAÇÃO EXCLUSIVA (RENDIMENTO LÍQUIDO)</div>
    <table class="table">
        <tr>
            <td width="80%">01. Décimo Terceiro Salário</td>
            <td class="text-right">{{ number_format($n13Salario, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>02. Imposto de Renda Retido na Fonte sobre o 13º Salário</td>
            <td class="text-right">{{ number_format($rendimento->irrf_13, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>03. Outros (especificar):</td>
            <td class="text-right">0,00</td>
        </tr>
    </table>

    <div class="section-title">6. INFORMAÇÕES COMPLEMENTARES</div>
    <table class="table">
        <tr>
            <td height="60px" valign="top">
                @if($rendimento->plano_saude > 0)
                Plano de Saúde: {{ number_format($rendimento->plano_saude, 2, ',', '.') }}<br>
                @endif
                @if($rendimento->pensionistas)
                Pensionistas: {{ $rendimento->pensionistas }}<br>
                @endif
                Matrícula(s): {{ str_replace(['{', '}'], '', $rendimento->regist) }}
            </td>
        </tr>
    </table>

    <div class="section-title">7. RESPONSÁVEL PELAS INFORMAÇÕES</div>
    <table class="table">
        <tr>
            <td width="50%">
                <span class="label">NOME:</span> {{ $instituicao->nomeinst }}<br>
                <span class="label">DATA:</span> {{ date('d/m/Y') }}
            </td>
            <td class="text-center">
                <br><br>
                ____________________________________________________<br>
                Assinatura
            </td>
        </tr>
    </table>
</body>
</html>
