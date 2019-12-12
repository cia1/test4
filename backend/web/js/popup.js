/*jshint globalstrict: true*/
"use strict";

var popup = (function (contentContainer) {
    var _panel;
    var _title;
    var _container;
    var _prevContainer;
    var _prevContent;

    function _resetContainer() {
        if (_prevContainer !== undefined && _prevContent !== undefined) {
            _prevContainer.appendChild(_prevContent);
            _prevContainer = _prevContent = undefined;
        }
    }

    var title = function (title) {
        _title.innerHTML = title;
        return popup;
    };
    var show = function () {
        _panel.className = 'popup shown';
        return popup;
    };
    var hide = function () {
        _panel.className = 'popup hidden';
        return popup;
    };

    var fromContent = function (content) {
        _resetContainer();
        _container.innerHTML = content;
        return popup;
    };
    var fromContainer = function (container) {
        _resetContainer();
        container.style.display = 'block';
        _container.innerHTML = '';
        _prevContainer = container.parentNode;
        _prevContent = container;
        _container.appendChild(container);
        return popup;
    };

    function _createPanel() {
        _panel = document.createElement('div');
        _panel.className = 'popup hidden';
        _panel.innerHTML = '<div><div class="close">X</div><h3></h3><div class="content"></div></div>';
        _title = _panel.getElementsByTagName('h3')[0];
        _container = _panel.getElementsByClassName('content')[0];
        _panel.getElementsByClassName('close')[0].onclick = function () {
            hide();
        };
        contentContainer.appendChild(_panel);
    }

    _createPanel();

    return {
        'show': show,
        'hide': hide,
        'title': title,
        'fromContent': fromContent,
        'fromContainer': fromContainer
    };

})(document.body);