import documentTypesList from './documentType.js';
import { isEmptyValue } from './utils.js'

const controllerPath = 'controller/example.php';
const returnResponse = function(response) {
  return response.json();
  // return response.text();
};

const selectElement = document.getElementById('documentTypesList');
const documentNumberElement = document.getElementById('documentNumber');
const digitValidatorElement = document.getElementById('digitValidator');


document.addEventListener('DOMContentLoaded', function() {
  documentTypesList.forEach(function(optionItem) {
    // add new options to select
    const optionElement = document.createElement('option');
    optionElement.value = optionItem.value;
    optionElement.text = optionItem.name;
    if (optionElement.value === 'V') {
      optionElement.selected = true;
    }

    selectElement.appendChild(optionElement);
  });

  const buttonSearchButton = document.getElementById('searchPerson');
  buttonSearchButton.addEventListener('click', searchPerson);
});


function searchPerson() {
  const documentNumber = documentNumberElement.value;
  const documentType = selectElement.value;
  const digitValidator =  digitValidatorElement.value;

  const isValidForm = validForm({
    documentNumber,
    documentType,
    digitValidator
  });

  if (!isValidForm) {
    return;
  }

  const dataSend = new FormData();
  dataSend.append('documentNumber', documentNumber);
  dataSend.append('documentType', documentType);
  dataSend.append('digitValidator', digitValidator);

  fetch(controllerPath, {
    method: 'POST',
    body: dataSend,
    cache: 'no-cache'
  })
  .then(returnResponse)
  .then(function(data) {
    console.log('data', data)
  })
  .catch(function(error) {
    console.error(error);
  });
}


function validForm({
  documentNumber,
  documentType,
  digitValidator
}) {
  if (isEmptyValue(documentNumber)) {
    return false;
  }

  return true;
}
