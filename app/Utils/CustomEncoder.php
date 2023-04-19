<?php

namespace App\Utils;
class CustomEncoder
{
    /**
     * Encode a string with the enterprice rules
     * @return string
     */
    public static function encode(string $password) : string
    {
        $cad = "";
        for ($i = 0; $i < strlen($password); $i++) {
            $cad = $cad . (ord($password[$i]) * 666) . "-";
        }
        return $cad;
    }

    /**
     * Decode a string with the enterprice rules
     * @return string
     */
    public static function decode(string $password) : string
    {
        $arreglo = explode("-", $password);
        $cad = "";
        if (count($arreglo) > 1) {
            for ($i = 0; $i < count($arreglo) - 1; $i++) {
                $cad = $cad . chr(($arreglo[$i] / 666));
            }
        }
        else {
            for ($i = 0; $i < count($arreglo); $i++) {
                $cad = $cad . chr(($arreglo[$i] / 666));
            }
        }
        return $cad;
    }
}
