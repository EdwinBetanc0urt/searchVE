<?php

trait SENIAT {

    protected static $URL_SENIAT = "http://contribuyente.seniat.gob.ve/getContribuyente/getrif";

    protected static $URN_SENIAT = array (
        "rif" => null
    );

    protected static $URL_SENIAT_2 = "http://contribuyente.seniat.gob.ve/BuscaRif/BuscaRif.jsp";

    protected static $URN_SENIAT_2 = array (
        "p_rif" => null
    );

    protected static $responseCodesSENIAT = array(
        // code_result:
        "-1" => "no hay soporte a curl",
        "0" => "no hay conexion a internet",
        "1" => "existe rif consultado",
        // otherwise:
        "450" => "formato de rif invalido",
        "452" => "rif no existe"
    );


    public static function getUrnSeniat(
        $identifierNumber,
        $documentType = "V",
        $validityDigit = null
    ) {
        $urn = self::$URN_SENIAT_2;
        // is case sensitive in nationality, must be capital uppercase
        $documentType = strtoupper($documentType);

        $rif = self::getRIF($identifierNumber, $documentType, $validityDigit);

        $urn["p_rif"] = $rif;

        return $urn;
    }

    /**
     * Calculates the last digit of the rif from the card
     * Based on the method module 11 for the calculation of the verification digit
     * and applying the own modifications executed by seniat
     * @link http://es.wikipedia.org/wiki/C%C3%B3digo_de_control#C.C3.A1lculo_del_d.C3.ADgito_verificador
     * @param integer $identifierNumber Taxpayer's identification number
     * @param string $documentType taxpayer's document type
     * @return integer $validityDigit verification digit
     */
    public static function calculateDigitValidator(
        $identifierNumber,
        $documentType = "V"
    ) {
        $documentType = strtoupper($documentType);
        $length = strlen($identifierNumber); // counts the size of the identity card

        // if the size of the card is greater than 9 characters or less than 3
        if ($length > 9 || $length <= 3) {
            return false;
        }

        if ($length == 9) {
            $length--;
        }

        $calc = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $constantValues = array(4, 3, 2, 7, 6, 5, 4, 3, 2);

        switch ($documentType) {
            case "V":
                $calc[0] = 1;
                break;
            case "E":
                $calc[0] = 2;
                break;
            case "J":
                $calc[0] = 3;
                break;
            case "P":
                $calc[0] = 4;
                break;
            case "G":
                $calc[0] = 5;
                break;
            case "C":
                $calc[0] = 6;
                break;
        }

        $sum = $calc[0] * $constantValues[0];
        $index = count($constantValues) - 1;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = $calc[$index] = intval($identifierNumber[$i]);
            $sum += $digit * $constantValues[$index--];
        }

        $validityDigit = $sum % 11;

        if ($validityDigit > 1) {
            $validityDigit = 11 - $validityDigit;
        }
        return $validityDigit;
    }


    /**
     * Returns the RIF, if it is sent complete it validates it, if the validating
     * digit is missing it adds it
     * @return string o bool, Complete RIF or False you do not coincide
     */
    public static function getRIF(
        $identifierNumber,
        $documentType = "V",
        $validityDigit = null
    ) {
        $documentType = strtoupper($documentType);

        if ($validityDigit == "") {
            $validityDigit = self::calculateDigitValidator($identifierNumber, $documentType);
        }

        $rif = $documentType . $identifierNumber . $validityDigit;
        if (self::validateRif($rif)) {
            return $rif;
        }

        return false;
    } // cierre de la función


    /**
     * Check if the rif is correct based on the validation digit
     * @param string $rif, Taxpayer Tax Information Registry
     * @return bool, If the format and the last digit of the RIF is well validated
     */
    public static function validateRif($rif = "")
    {
        $rif = strtoupper($rif);

        // stores the format of the card [documentType][identifierNumber][validityDigit]
        $returned = preg_match("/^([VEJPGC]{1})([0-9]{9}$)/", $rif);

        if ($returned) {
            $digits = str_split($rif);

            $digits[8] *= 2;
            $digits[7] *= 3;
            $digits[6] *= 4;
            $digits[5] *= 5;
            $digits[4] *= 6;
            $digits[3] *= 7;
            $digits[2] *= 2;
            $digits[1] *= 3;

            // Determine special digit according to the initial of the RIF
            // Rule introduced by the SENIAT
            switch ($digits[0]) {
                case "V":
                    $specialDigit = 1;
                    break;
                case "E":
                    $specialDigit = 2;
                    break;
                case "J":
                    $specialDigit = 3;
                    break;
                case "P":
                    $specialDigit = 4;
                    break;
                case "G":
                    $specialDigit = 5;
                    break;
                case "C":
                    $specialDigit = 6;
                    break;
            }

            $sum = (array_sum($digits) - $digits[9]) + ($specialDigit * 4);
            $residue = $sum % 11;
            $subtract = 11 - $residue;

            $validityDigit = ($subtract >= 10) ? 0 : $subtract;

            if ($validityDigit != $digits[9]) {
                $returned = false;
            }
        }
        if ($returned == 0) {
            return false;
        }
        return true;
    }


    /**
     * Consult the data of the person who is registered in the SENIAT
     * @param integer $identifierNumber, indicates the document number (maxlength 8 characteres)
     * @param string $documentType
     * @param integer $validityDigit, indicates the number of the verification digit
     * @return string response of the data associated with the person
     */
    public static function searchSENAT(
        $identifierNumber,
        $documentType = "V",
        $validityDigit = null
    ) {
        $urn_seniat = self::getUrnSeniat($identifierNumber, $documentType, $validityDigit);
        $rif = $urn_seniat["p_rif"];
        $urn_seniat = http_build_query($urn_seniat);

        $uri_seniat = self::$URL_SENIAT . "?" . $urn_seniat;

        // it does not matter if the nationality or type of document is in upper or lower case
        // $url_seniat = "http://contribuyente.seniat.gob.ve/getContribuyente/getrif?rif=" . $rif;
        $response = @file_get_contents($uri_seniat);

        if ($response) {
            try {
                if (substr($response, 0, 1) != "<") {
                    throw new Exception($response);
                }
                $xml = simplexml_load_string($response);
                if (!is_bool($xml)) {
                    $resource = $xml->children("rif");

                    $responseSeniat = array();
                    $responseSeniat["error"] = 0;

                    foreach ($resource as $node) {
                        $index = strtolower($node->getName());
                        $responseSeniat[$index] = (string) $node;
                    }
                    $responseSeniat["mensaje"] = "Consulta satisfactoria SENIAT";

                    // stores name and surname data in the position it deems appropriate
                    $names = self::getNameAndSurname($responseSeniat["nombre"]);
                }
            } catch (Exception $e) {
                $response = explode(" ", @$response, 2);
                $responseSeniat["error"] = (int) $response[0];
            }
        } else {
            $responseSeniat = array(
                "agenteretencioniva" => null,
                "contribuyenteiva" => null,
                "tasa" => null
            );
            $names = self::getNameAndSurname();

            $documentTypeExtracted = substr($rif, 0, 1); // type of document (V, E, J, G)
            $identifierNumberExtracted = substr(substr($rif, 1), 0, -1);

            $digitCurrent = substr($rif, -1);
            $digitCalculated = self::calculateDigitValidator($documentTypeExtracted, $identifierNumberExtracted);

            $responseSeniat["mensaje"] = "450 El Rif del Contribuyente No es válido, SENIAT ";
            if ($digitCurrent ==  $digitCalculated) {
                $responseSeniat["mensaje"] = "452 El Contribuyente no está registrado en el SENIAT o no hay conexión";
            }

            $responseSeniat["error"] = $names["error"] + 1;
        }

        return array_merge($names, $responseSeniat);
    }


}
