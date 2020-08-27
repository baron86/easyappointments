window.BackendPackages = window.BackendPackages || {};

(function (exports) {

    'use strict';
    
    var helper = {};
    
    exports.initialize = function (defaultEventHandlers) {
        defaultEventHandlers = defaultEventHandlers || false;

        helper = new PackagesHelper();
        helper.resetForm();
        helper.filter('');
        
        // Fill the services and providers list boxes.
        var html = '<div>';
        $.each(GlobalVariables.services, function (index, service) {
            html +=
                '<div class="checkbox">' +
                '<label class="checkbox">' +
                '<input type="checkbox" data-id="' + service.id + '" />' +
                service.name +
                '</label>' +
                '</div>';

        });
        html += '</div>';
        $('#package-services').html(html);

        if (defaultEventHandlers) {
            _bindEventHandlers();
        }
    };
    
    function _bindEventHandlers() {
        helper.bindEventHandlers();
    }

})(window.BackendPackages);
