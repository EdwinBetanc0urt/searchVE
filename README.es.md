# Buscar en Venezuela
Una pequeña librería que realiza búsquedas de nombres de personas mediante la nacionalidad, el número de identificación, también el registro de información fiscal (RIF) y en algunos casos la fecha de nacimiento.

[English](./README.md) | Español


## Características
* Calcula el dígito de verificación por medio del número de identificación y el tipo de documento.
* Obtiene los datos del Servicio Nacional Integrado de Administración Aduanera y Tributaria (SENIAT) a través del registro de información fiscal (RIF).
* Obtiene los datos del Centro Nacional Electoral (CNE) si está registrado mediante la nacionalidad y el número de identificación.
* Obtiene los datos del Instituto Venezolano de Seguridad Social (IVSS), si recibe pensiones, por medio de la nacionalidad y el número de identificación.
* Obtiene los datos de la cuenta individual del Instituto Venezolano de Seguridad Social (IVSS) si está registrada o asegurada por un empleador, mediante la nacionalidad, el número de identificación y la fecha de nacimiento.
* Reordena los nombres y apellidos que se pueden obtener, separando el primer nombre, segundo nombre, primer apellido y segundo apellido.


## API de aplicación


## Requerimientos
* PHP en su versión 5.6 o superior.
* Biblioteca cURL habilitada.


## Por hacer
* Obtener los datos del INTT a través de la nacionalidad, el número de identificación y la fecha de nacimiento.
