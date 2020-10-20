# Search Venezuela
A small library that performs searches of people's names by nationality, identification number, also the tax information register (RIF) and in some cases the date of birth.

English | [Spanish](./README.es.md)


## Features
* Calculate the verification digit by means of the identification number and the type of document.
* It obtains the data from the Integrated National Service of Customs and Tax Administration (SENIAT) through the fiscal information registry (RIF).
* It obtains the data from the National Electoral Center (CNE) if it is registered by means of the nationality and the identification number.
* It obtains the data from the Venezuelan Institute of Social Security (IVSS) pensions, if it receives pensions, by means of the nationality and the identification number.
* Obtains data from the Venezuelan Institute of Social Security (IVSS) individual account if registered or insured by an employer, by means of the nationality, the identification number and the date of birth.
* Re-order the names and surnames that can be obtained, separating the first name, second name, first surname and second surname.


## Implementation API


## Requirements
* PHP in its version 5.6 or higher.
* cURL library enabled.


## To be done
* Obtain the data from the INTT through the identity card and date of birth.
