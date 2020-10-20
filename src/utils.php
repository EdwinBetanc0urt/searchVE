<?php

trait Utils {

    private static function cUrlSession()
    {
        $cUrlSession = curl_init(); // start cURL session
        curl_setopt($cUrlSession, CURLOPT_TIMEOUT, 30); // maximum seconds allowed for cURL timeout
        curl_setopt($cUrlSession, CURLOPT_RETURNTRANSFER, true); // returns the result of the transfer as a string instead of displaying it directly
        curl_setopt($cUrlSession, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($cUrlSession, CURLOPT_HEADER, true); // include the header in the output
        curl_setopt($cUrlSession, CURLINFO_HEADER_OUT, true);

        return $cUrlSession;
    }


    public static function httpGetRequest($url, $data = [])
    {
        $uri = $url;
        $cUrlSession = self::cUrlSession(); // get a cURL session with the pre-set parameters

        $urn = http_build_query($data);
        if (!self::isEmptyValue($urn)) {
            $uri .= "?" . $urn;
        }
        curl_setopt($cUrlSession, CURLOPT_URL, $uri); // sets the uri
        $getResponse = curl_exec($cUrlSession);
        curl_close($cUrlSession);

        return $getResponse;
    }


    public static function httpPostRequest($url, $data = [])
    {
        $cUrlSession = self::cUrlSession(); // get a cURL session with the pre-set parameters
        curl_setopt($cUrlSession, CURLOPT_URL, $url); // sets the uri

        curl_setopt($cUrlSession, CURLOPT_POST, 1); // indicates that it is a request by the POST method
        $data = http_build_query($data); // is converted to an urlencoded string
        curl_setopt($cUrlSession, CURLOPT_POSTFIELDS, $data); // sets the fields of the request

        $posResponse = curl_exec($cUrlSession);
        curl_close($cUrlSession);

        return $posResponse;
    }


    /**
     * Allows to clean the values of the carriage return (\n \r \t)
     * @param string $content Value we want to clean from not allowed characters
     * @return string Returns the same values but without the carriage return
     */
    public static function getCleanField($content)
    {
        $coincidence = array("\n", "\t");
        $r = trim(str_replace($coincidence, " ", $content));
        $vsLimpio = str_replace("\r", "", str_replace("\n", "", str_replace("\t", "", $r)));

        return $vsLimpio;
    }


    public static function getArrayClean($list)
    {
        foreach($list as $key => $value) {
            $list[$key] = trim($value);
        }

        return $list;
    }


    /**
     * Sort and identify names and surnames using pre-saved words from the natural
     * language in Spanish to identify when they are part of the first or second
     * name and/or first or second surname.
     * @param string $names, Chain obtained from the consultation with the names and surnames
     * @return array $responseData, Returns the same values ordered in each place
     */
    public static function getNameAndSurname($names = "")
    {
        $responseData = array(
            0 => null,
            "primer_nombre" => null,

            1 => null,
            "segundo_nombre" => null,

            2 => null,
            "primer_apellido" => null,

            3 => null,
            "segundo_apellido" => null,

            4 => null,
            "razon_social" => null,

            5 => 0,
            "error" => 0
        );

        if ($names != "") {
            $data = explode(" ", self::getCleanField($names));
            $responseData = array();

            // $part = "DEL" || "DE" || "LOS" || "LAS" || "LA";
            // $not = " " || "";

            // identifies and evaluates the natural language in those positions
            // so that they are placed in the correct order, otherwise a name
            // like MARIA DE LOS ANGELES would occupy as a last name LOS ANGELES

            // places the second position
            if($data[1] == "DEL" || $data[1] == "DE") {
                if ($data[2] == "LOS" || $data[2] == "LAS") {
                    $secondName = $data[1] . " " . $data[2] . " " . $data[3];
                    //$secondName = $data;
                    $firstSurname = $data[4];
                    $secondSurname = $data[5];
                } else {
                    $secondName = $data[1] . " " . $data[2];
                    $firstSurname = $data[3];
                    $secondSurname = $data[4];
                }
            } elseif ($data[1] == "" || $data[1] == " ") {
                // no middle name
                $secondName = NULL;
                $firstSurname = $data[2];
                // she is married
                if($data[3] == "DEL" || $data[3] == "DE" || $data[3] == "" || $data[3] == " ") {
                    // has a compound surname
                    $secondSurname = $data[3] . " " . $data[4];
                } elseif($data[4] == "" || $data[4] == " ") {
                    $secondSurname = $data[4] . " " . $data[5];
                } else {
                    if (empty($data[3])) {
                        // does not have a second surname
                        $data[3] = "";
                    }
                    $secondSurname = $data[3];
                }
            } else {
                // is a common name
                $secondName = $data[1];
                $firstSurname = $data[2];
                if (empty($data[3])) {
                    // does not have a second surname
                    $data[3] = NULL;
                } elseif ($data[3] == "DEL" || $data[3] == "DE") {
                    // has a compound surname
                    $secondSurname = $data[3] . " " . $data[4];
                } else {
                    // the surname is normal
                    $secondSurname = $data[3];
                }
            }

            $responseData = array(
                0 => strtolower($data[0]),
                "primer_nombre" => strtolower($data[0]),

                1 => strtolower($secondName),
                "segundo_nombre" => strtolower($secondName),

                2 => strtolower($firstSurname),
                "primer_apellido" => strtolower($firstSurname),

                3 => strtolower($secondSurname),
                "segundo_apellido" => strtolower($secondSurname),

                4 => strtolower(
                    $data[0]. " " . $secondName . " " . $firstSurname . " " . $secondSurname
                ),
                "razon_social" => strtolower(
                    $data[0]. " " . $secondName . " " . $firstSurname . " " . $secondSurname
                ),

                5 => 0,
                "error" => 0
            );
        }

        return $responseData;
    }


    public static function isEmptyValue($value)
    {
        if (empty($value)) {
            return true;
        }

        if ($value == "") {
            return true;
        }

        if ($value == null) {
            return true;
        }

        return false;
    }

}
