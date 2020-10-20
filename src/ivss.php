<?php

trait IVSS {

    protected static $URL_IVSS_PENSIONS = "http://www.ivss.gob.ve:28080/Pensionado/PensionadoCTRL";

    protected static $URN_IVSS_PENSIONS = array(
        "boton" => "Consultar",
        "nacionalidad" => null,
        "cedula" => null,
        // date of birth (optional)
        "d1" => null,
        "m1" => null,
        "y1" => null
    );

    protected static $URL_IVSS_INDIVIDUAL_ACCOUNT = "http://www.ivss.gob.ve:28083/CuentaIndividualIntranet/CtaIndividual_PortalCTRL";

    public static $URN_IVSS_INDIVIDUAL_ACCOUNT = array(
        "boton" => "Consultar",
        "nacionalidad_aseg" => null,
        "cedula_aseg" => null,
        // date of birth (required)
        "d" => null,
        "m" => null,
        "y" => null
    );

    protected static $REPLACE_IVSS_PENSIONS = array(
        "Tipo de Pensi&oacute;n:", "Via:", "C&eacute;dula:", "Apellido y nombre:",
        "Entidad Financiera:", "Estatus de la Pensi&oacute;n:", "Tipo de Pensi&oacute;n:",
        "Fecha de Inactivaci&oacute;n:", "Monto de Pensi&oacute;n:", "Monto de Ajuste:",
        "Monto de Homologaci&oacute;n:", "Monto de Deuda:", "Total Abonado:",
        "Monto de Adeudado:", "Total Pagos:", "Total a Pagar este mes:"
    );


    //$url = "http://www.ivss.gob.ve:28080/Pensionado/PensionadoCTRL?boton=Consultar&nacionalidad=$nationality&cedula=$identifierNumber&d1=$day&m1=$mont&y1=$year";
    public static function getUriIvssPensions(
        $identifierNumber,
        $nationality = "V",
        $day =  null,
        $month = null,
        $year = null
    ) {
        $uri = self::$URN_IVSS_PENSIONS;
        // is case sensitive in nationality, must be capital uppercase
        $uri["nacionalidad"] = strtoupper($nationality);
        $uri["cedula"] = $identifierNumber;
        $uri["d1"] = $day;
        $uri["m1"] = $month;
        $uri["y1"] = $year;

        return implode($uri);
    }


    public static function getUriIvssIndividualAccount(
        $identifierNumber,
        $nationality = "V",
        $day =  "",
        $month = "",
        $year = ""
    ) {
        $uri = self::$URN_IVSS_INDIVIDUAL_ACCOUNT;
        $uri["nacionalidad_aseg"] = strtoupper($nationality);
        $uri["cedula_aseg"] = $identifierNumber;
        $uri["d"] = $day;
        $uri["m"] = $month;
        $uri["y"] = $year;

        return $uri;
    }


    /**
     * Consult the data of the Person who has a pension in the IVSS
     * @param integer $identifierNumber, indicates the document number (maxlength 8 characteres)
     * @param string $documentType, indicates nationality or type of document (V, E)
     * @param string $day, day of birth
     * @param string $month, month of birth
     * @param string $year, year of birth
     * @return array $dataResponse, responde frem query
     */
    public static function searchIVSSPensioners(
        $identifierNumber,
        $nationality = "V",
        $day =  "",
        $month = "",
        $year = ""
    ) {
        // is case sensitive in nationality, must be capital uppercase
        $nationality = strtoupper($nationality);

        $requestBody = self::getUriIvssPensions($identifierNumber, $nationality, $day, $month, $year);

        $resource = self::httpGetRequest($requestBody); // get all the content of the website
        $text = strip_tags($resource); // remove html tags and leave only text
        $findme = "Consulta de Pensiones en Linea"; // searches the string in the obtained text, in case you find
        $pos = strpos($text, $findme);

        $findme2 = "no tiene"; // searches for the string in the text obtained, in case it is not found
        $pos2 = strpos($text, $findme2);

        if ($pos == true AND $pos2 == false) {
            // search for these words in the text (use &acute; instead of accents because that's how the page brings it)
            $rempl = self::$REPLACE_IVSS_PENSIONS;
            $r = trim(str_replace($rempl, "|", self::getCleanField($text)));
            $data = explode("|", $r);
            $names = self::getNameAndSurname($data[4]);

            // creates the response array
            $dataResponse = array(
                "mensaje" => "Consulta de Datos IVSS Satisfactoria",
                "error" => 0,
                "nacionalidad" => $nationality,
                "cedula" => $identifierNumber,
                "pensionado" => "SI",
                "tipo_pension" => self::getCleanField($data[1]),
                "via" => self::getCleanField($data[2]),
                "entidad_financiera" => self::getCleanField($data[5]),
                "estatus" => self::getCleanField($data[6]),
                "fecha_inactivacion" => self::getCleanField($data[7]),
                "monto_pension" => self::getCleanField($data[8]),
                "monto_ajuste" => self::getCleanField($data[9]),
                "monto_homologacion" => self::getCleanField($data[10]),
                "monto_deuda" => self::getCleanField($data[11]),
                "total_abonado" => self::getCleanField($data[12]),
                "monto_adeudado" => self::getCleanField($data[13]),
                "total_pagos" => self::getCleanField($data[14]),
                "total_mes" => self::getCleanField($data[15])
            );
        } else {
            $names = self::getNameAndSurname();

            $dataResponse = array(
                "mensaje" => "EL CIUDADANO no tiene Pension Asociada, IVSS",
                "nacionalidad" => $nationality,
                "cedula" => $identifierNumber,
                "nombres" => null,
                "apellidos" => null,
                "pensionado" => "NO"
            );
            $dataResponse["error"] = $names["error"] + 1;
        }
        return array_merge($names, $dataResponse);
    }


    public static function searchIVSSIndividualAccount(
        $identifierNumber,
        $nationality = "V",
        $day = "",
        $month = "",
        $year = ""
    ) {
        // is case sensitive in nationality, must be capital uppercase
        $nationality = strtoupper($nationality);

        $data = self::getUriIvssIndividualAccount($identifierNumber, $nationality, $day, $month, $year);
        $resource = self::httpPostRequest(self::$URL_IVSS_INDIVIDUAL_ACCOUNT, $data);
        $text = strip_tags($resource);
        return $text;
    }


    public static function  searchIVSS(
        $identifierNumber,
        $nationality = "V",
        $day =  "",
        $month = "",
        $year = ""
    ) {
        self::searchIVSSIndividualAccount($identifierNumber, $nationality, $day, $month, $year);
        self::searchIVSSPensioners($identifierNumber, $nationality, $day, $month, $year);
    }
}
