<?php

trait CNE {

    protected static $URL_CNE = "http://www.cne.gob.ve/web/registro_electoral/ce.php";

    protected static $URN_CNE = array(
        "nacionalidad" => null,
        "cedula" => null
    );

    protected static $reasonsCNE = array(
        "SERIAL ANULADO (2)",
        "FALLECIDO (3)"
    );

    protected static $REPLACE_CNE = array(
        "Cédula:", "Nombre:", "Estado:", "Municipio:", "Parroquia:",
        "Centro:", "Dirección:", "Cerrar", "Usted", "cargo de",
        "de la mesa", "Si desea solicitar", "Imprimir", "ESTATUS",
        "REGISTRO ELECTORAL - ", "Planilla", "Consulta de Datos",
        "DATOS PERSONALES", "Objeción:", "Descripción:"
    );


    /**
     * Obtains the URI CNE with the identification data
     * @param string|integer $identifierNumber
     * @param string $nationality, is case sensitive in nationality, must be capital uppercase
     * @return string
     */
    private static function getUrnCne(
        $identifierNumber,
        $nationality = "V"
    ) {
        $uri = self::$URN_CNE;
        $uri["nacionalidad"] = strtoupper($nationality);
        $uri["cedula"] = $identifierNumber;
        return http_build_query($uri);
    }


    /**
     * Browse the data of people registered at the National Electoral Center (Centro Nacional Electoral - CNE)
     * @param integer $identifierNumber Voting card of the person
     * @param string $nationality Nationality of the person voting
     * @return array response of the data associated with the person
     * TODO: Validate if documentType is a string letter
     */
    public static function searchCNE(
        $identifierNumber,
        $nationality = "V"
    ) {
        // is case sensitive in nationality, must be capital uppercase
        $nationality = strtoupper($nationality);

        $requestBody = self::getUrnCne($identifierNumber, $nationality);
        $resource = self::httpGetRequest(self::$URL_CNE, $requestBody); // get all the content of the website
        $text = strip_tags($resource); // remove html tags and leave only text

        $findme = "REGISTRO ELECTORAL"; // searches the string in the obtained text, in case you find
        $pos = strpos($text, $findme);

        $findme2 = "ADVERTENCIA"; // searches for the string in the text obtained, in case it is not found
        $pos2 = strpos($text, $findme2);
        if ($pos == TRUE AND $pos2 == FALSE) {
            $cedula = $nationality . "-" . $identifierNumber;
            // TODO: Add $cedula to array
            $rempl = self::$REPLACE_CNE;

            $textClean = self::getCleanField($text);
            $r = trim(str_replace($rempl, "|", $textClean));
            $data = explode("|", $r);

            $data = self::getArrayClean($data);
            $in = in_array("El número de cédula ingresado no corresponde a un elector", $data);
            if ($in) {
                $names = self::getNameAndSurname();
            } else {
                $names = self::getNameAndSurname($data[3]);
            }

            // TODO: IMPROVE CONDITIONAL
            if ($data[2] == "") {
                $message = $data[4];
                if (strpos($data[7], "(")) {
                    $message = $message . ". " . $data[7];
                    // $names = self::getNameAndSurname();
                }
                $dataResponse = array(
                    "error" => 0,
                    "mensaje" => $message,
                    "estado" => "",
                    "municipio" => "",
                    "parroquia" => "",
                    "centro" => "",
                    "direccion" => ""
                    //"servicio" => self::getCleanField($data[9])
                    //"cargo" => self::getCleanField($data[11]),
                    //"mesa" => self::getCleanField($data[12])
                );
            } else {
                $dataResponse = array(
                    "error" => 0,
                    "mensaje" => "Consulta de Datos CNE Satisfactoria",
                    "estado" => substr($data[4], 5),
                    "municipio" => substr($data[5], 4),
                    "parroquia" => substr($data[6], 4),
                    "centro" => $data[7],
                    "direccion" => self::getCleanField($data[8])
                    //"servicio" => self::getCleanField($data[9])
                    //"cargo" => self::getCleanField($data[11]),
                    //"mesa" => self::getCleanField($data[12])
                );
            }
        } else {
            $names = self::getNameAndSurname();
            if ($nationality == "V" OR $nationality == "E") {
                $dataResponse = array(
                    "mensaje" => "Error, no está registrado en el CNE o no hay conexión"
                );
            } else {
                $dataResponse = array(
                    "mensaje" => "Error, el documento de identidad no es el correcto"
                );
            }
            // the number of the card entered does not correspond to a voter
            // The data is NOT correct
            $dataResponse["error"] = $names["error"] + 1;
        }
        return array_merge($names, $dataResponse);
    }


}
