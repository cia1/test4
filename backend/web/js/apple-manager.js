/*jshint globalstrict: true*/
/*exported appleFall, appleEat, appleDelete */
"use strict";

document.getElementById('create-apple').onclick = function () {
    window.popup.fromContainer(document.getElementById('apple-create')).title('Добавление яблок').show();
    return false;
};

function appleFall(id) {
    var form = document.getElementById('apple-fall');
    form['AppleActionForm[id]'].value = id;
    window.popup.fromContainer(form).title('Сорвать яблоко?').show();
    document.querySelector('.popup .help-block').innerHTML = '';
}

function appleEat(id) {
    var form = document.getElementById('apple-eat');
    form['AppleActionForm[id]'].value = id;
    window.popup.fromContainer(form).title('Откусить').show();
    document.querySelector('.popup .help-block').innerHTML = '';
}

function appleDelete(id) {
    var form = document.getElementById('apple-delete');
    form['AppleActionForm[id]'].value = id;
    window.popup.fromContainer(form).title('Выбросить?').show();
    document.querySelector('.popup .help-block').innerHTML = '';
}