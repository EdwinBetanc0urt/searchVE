<?php

require_once("cne.php");
require_once("intt.php");
require_once("ivss.php");
require_once("seniat.php");
require_once("utils.php");

/**
 * Search for data on people in the Venezuelan government database
 *
 * @author: Edwin Betancourt <EdwinBetanc0urt@outlook.com>
 * @license: GNU GPL v3, General Public License 3.
 * @category Libreria
 * @package: searchVE.php
 */
class SearchVE
{
    use CNE, INTT, IVSS, SENIAT, Utils;

    /**
     * Search all possible data
     * @param integer $identifierNumber, indicates the document number (maxlength 8 characteres)
     * @param string $documentType, indicates nationality or type of document (V, E, J, P, G, C)
     * @param integer $validityDigit, indicates the number of the verification digit
     * @return array $allData, responde frem all queries
     */
    public static function search(
        $documentType,
        $identifierNumber = "V",
        $validityDigit = null,
        // birthday (optional)
        $day =  null,
        $month = null,
        $year = null
    ) {
        $cneData = self::searchCNE($documentType, $identifierNumber);
        $ivssData = self::searchIVSS($documentType, $identifierNumber, $day, $month, $year);
        $seniatData = self::searchSENAT($documentType, $identifierNumber, $validityDigit);
        $errors = $cneData["error"] + $ivssData["error"] + $seniatData["error"];

        $allData = array(
            "error" => $errors,
            "CNE" => $cneData,
            "IVSS" => $ivssData,
            "SENIAT" => $seniatData
        );
        return  $allData;
    }


    /**
     * It makes consultations taking the data of the constructor and it compares
     * where there are consultations to take the names and last names since it
     * can not have data registered in an organization but in another one.
     * @return array
     */
    public static function searchNames(
        $documentType,
        $identifierNumber = "V",
        $validityDigit = null,
        // birthday (optional)
        $day =  null,
        $month = null,
        $year = null
    ) {
        $documentType = trim(strtoupper($documentType));
        if (in_array($documentType, ["V", "E"])) {
            $cneData = self::searchCNE($documentType, $identifierNumber);
            if (!self::isEmptyValue($cneData["primer_nombre"])) {
                return $cneData;
            }

            $ivssData = self::searchIVSS($documentType, $identifierNumber, $day, $month, $year);
            if (!self::isEmptyValue($ivssData["primer_nombre"])) {
                return $ivssData;
            }
        }

        $seniatData = self::searchSENAT($documentType, $identifierNumber, $validityDigit);
        if (!self::isEmptyValue($seniatData["primer_nombre"])) {
            return $seniatData;
        }

        return self::getNameAndSurname();
    }

}
