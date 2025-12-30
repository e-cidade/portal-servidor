<?php

namespace App\Helpers;

use DateTime;

class FormatHelper
{
    public static function format(
        ?string $str,
        string $tipo,
        string $caracter = " ",
        int $quantidade = 0,
        string $TipoDePreenchimento = "e"
    ): ?string {
        if ($str === null || $str === '') {
            return null;
        }

        switch ($tipo) {
            case "cpf":
                return preg_replace(
                    "/(\d{3})(\d{3})(\d{3})(\d{2})/",
                    "$1.$2.$3-$4",
                    $str
                );

            case "cnpj":
                return preg_replace(
                    "/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/",
                    "$1.$2.$3/$4-$5",
                    $str
                );

            case "b":
                return $str ? 'S' : 'N';

            case "f":
                $valor = number_format((float)$str, 2, ",", ".");
                return str_pad($valor, $quantidade > 0 ? $quantidade : 15, $caracter, STR_PAD_LEFT);

            case "d":
                $data = DateTime::createFromFormat("Y-m-d", $str);
                return $data ? $data->format("d/m/Y") : $str;

            case "s":
                switch ($TipoDePreenchimento) {
                    case "d":
                        return str_pad($str, $quantidade, $caracter, STR_PAD_RIGHT);
                    case "a":
                        return str_pad($str, $quantidade, $caracter, STR_PAD_BOTH);
                    default:
                        return str_pad($str, $quantidade, $caracter, STR_PAD_LEFT);
                }

            case "v":
                if (strpos($str, ",") !== false) {
                    return str_replace(",", ".", str_replace(".", "", $str));
                }

                if (strpos($str, "-") !== false) {
                    $data = explode("-", $str);
                    return "{$data[2]}-{$data[1]}-{$data[0]}";
                }

                if (strpos($str, "/") !== false) {
                    $data = explode("/", $str);
                    return "{$data[2]}-{$data[1]}-{$data[0]}";
                }

                return $str;
        }

        return null;
    }

    // Atalhos úteis
    public static function cpf(string $str): ?string
    {
        return self::format($str, 'cpf');
    }

    public static function cnpj(string $str): ?string
    {
        return self::format($str, 'cnpj');
    }

    public static function data(string $str): ?string
    {
        return self::format($str, 'd');
    }

    public static function valor(string $str, int $quantidade = 0, string $caracter = " "): ?string
    {
        return self::format($str, 'f', $caracter, $quantidade);
    }
}
