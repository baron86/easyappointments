(function () {

    'use strict';

    /**
     * CustomersHelper Class
     *
     * This class contains the methods that are used in the backend customers page.
     *
     * @class CustomersHelper
     */
    function PackagesHelper() {
        this.filterResults = {};
    }
    
    PackagesHelper.prototype.bindEventHandlers = function () {
        var instance = this;

        /**
         * Event: Filter Customers Form "Submit"
         */
        $('#filter-customers form').submit(function (event) {
            var key = $('#filter-customers .key').val();
            $('#filter-customers .selected').removeClass('selected');
            instance.resetForm();
            instance.filter(key);
            return false;
        });

        /**
         * Event: Filter Customers Clear Button "Click"
         */
        $('#filter-customers .clear').click(function () {
            $('#filter-customers .key').val('');
            instance.filter('');
            instance.resetForm();
        });

        /**
         * Event: Filter Entry "Click"
         *
         * Display the customer data of the selected row.
         */
        $(document).on('click', '.entry', function () {
            if ($('#filter-packages .filter').prop('disabled')) {
                return; // Do nothing when user edits a customer record.
            }

            var packageId = $(this).attr('data-id');
            var package1 = {};
            $.each(instance.filterResults, function (index, item) {
                if (item.id == packageId) {
                    package1 = item;
                    return false;
                }
            });

            instance.display(package1);
            $('#filter-packages .selected').removeClass('selected');
            $(this).addClass('selected');
            $('#edit-package, #delete-package').prop('disabled', false);
        });

        /**
         * Event: Add Customer Button "Click"
         */
        $('#add-package').click(function () {            
            instance.resetForm();
            $('.add-edit-delete-group').hide();
            $('.save-cancel-group').show();
            $('.record-details').find('input, textarea').prop('readonly', false);
            $('#package-services input:checkbox').prop('disabled', false);
            $('#filter-packages button').prop('disabled', true);
            $('#filter-packages .results').css('color', '#AAA');
        });

        /**
         * Event: Edit Customer Button "Click"
         */
        $('#edit-package').click(function () {
            $('.record-details').find('input, textarea').prop('readonly', false);
            $('.add-edit-delete-group').hide();
            $('.save-cancel-group').show();
            $('#package-services input:checkbox').prop('disabled', false);
            $('#filter-customers button').prop('disabled', true);
            $('#filter-customers .results').css('color', '#AAA');
        });

        /**
         * Event: Cancel Customer Add/Edit Operation Button "Click"
         */
        $('#cancel-package').click(function () {
            var id = $('#package-id').val();
            instance.resetForm();
            if (id != '') {
                instance.select(id, true);
            }
        });

        /**
         * Event: Save Add/Edit Customer Operation "Click"
         */
        $('#packages').on('click', '#save-package', function () {
            var packageObj = {
                name: $('#package-name').val(),
                description: $('#package-description').val(),
                units: $('#package-class-no').val(),
                price: $('#package-price').val()
            };
            // Include provider services.
            packageObj.services = [];
            $('#package-services input:checkbox').each(function () {
                if ($(this).prop('checked')) {
                    packageObj.services.push($(this).attr('data-id'));
                }
            });

            // Include id if changed.
            if ($('#package-id').val() !== '') {
                packageObj.id = $('#package-id').val();
            }

            if (!instance.validate()) {
                return;
            }

            instance.save(packageObj);
        }.bind(instance));

        /**
         * Event: Delete Customer Button "Click"
         */
        $('#delete-package').click(function () {
            var packageId = $('#package-id').val();
            var buttons = [
                {
                    text: EALang.delete,
                    click: function () {
                        instance.delete(packageId);
                        $('#message_box').dialog('close');
                    }
                },
                {
                    text: EALang.cancel,
                    click: function () {
                        $('#message_box').dialog('close');
                    }
                }
            ];
            GeneralFunctions.displayMessageBox(EALang.delete_package,
                EALang.delete_record_prompt, buttons);
        });
    };
    
    PackagesHelper.prototype.resetForm = function () {
        $('#filter-packages .selected').removeClass('selected');
        $('#filter-packages button').prop('disabled', false);
        $('#filter-packages .results').css('color', '');
        $('#packages .add-edit-delete-group').show();
        $('#packages .save-cancel-group').hide();
        $('#packages .record-details h3 a').remove();
        $('#packages .record-details').find('input, textarea').prop('readonly', true);
        $('#packages .record-details').find('select').prop('disabled', true);
        $('#packages .form-message').hide();
        $('.record-details .has-error').removeClass('has-error');
        $('#package-services input:checkbox').prop('disabled', true);
        $('#packages .add-break, #reset-working-plan').prop('disabled', true);
        $('#edit-package, #delete-package').prop('disabled', true);
        $('#packages .record-details').find('input, textarea').val('');
        $('#packages input:checkbox').prop('checked', false);
        $('#package-services input:checkbox').prop('checked', false);
        $('#package-services a').remove();
    };
    
    
    PackagesHelper.prototype.display = function (packageObj) {
        $('#package-id').val(packageObj.id);
        $('#package-name').val(packageObj.name);
        $('#package-price').val(packageObj.price);
        $('#package-class-no').val(packageObj.units);
        $('#package-description').val(packageObj.description);
        
        $('#package-services a').remove();
        $('#package-services input:checkbox').prop('checked', false);
        $.each(packageObj.services, function (index, serviceId) {
            $('#package-services input:checkbox').each(function () {
                if ($(this).attr('data-id') == serviceId.id) {
                    $(this).prop('checked', true);                    
                }
            });
        });        
    };
    
    PackagesHelper.prototype.select = function (id, display) {
        display = display || false;

        $('#filter-packages .selected').removeClass('selected');

        $('#filter-packages .entry').each(function () {
            if ($(this).attr('data-id') == id) {
                $(this).addClass('selected');
                return false;
            }
        });

        if (display) {
            $.each(this.filterResults, function (index, customer) {
                if (customer.id == id) {
                    this.display(customer);
                    $('#edit-package, #delete-package').prop('disabled', false);
                    return false;
                }
            }.bind(this));
        }
    };
    
    
    PackagesHelper.prototype.filter = function (key, selectId, display) {
        display = display || false;

        var postUrl = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_packages';
        var postData = {
            csrfToken: GlobalVariables.csrfToken,
            key: key
        };

        $.post(postUrl, postData, function (response) {
            if (!GeneralFunctions.handleAjaxExceptions(response)) {
                return;
            }

            this.filterResults = response;

            $('#filter-packages .results').html('');
            $.each(response, function (index, package1) {
                var html = this.getFilterHtml(package1);
                $('#filter-packages .results').append(html);
            }.bind(this));

            if (response.length == 0) {
                $('#filter-packages .results').html('<em>' + EALang.no_records_found + '</em>')
            }

            if (selectId != undefined) {
                this.select(selectId, display);
            }
        }.bind(this), 'json').fail(GeneralFunctions.ajaxFailureHandler);
    };
    
    PackagesHelper.prototype.getFilterHtml = function (package1) {
        var name = package1.name,
            info = package1.units+" classes",
            price ="R"+package1.price;

        var html =
            '<div class="package-row entry" data-id="' + package1.id + '">' +
            '<strong>' + name + '</strong><br>' +
            info + ', '+price+'<br>' +
            '</div><hr>';

        return html;
    };
    
    PackagesHelper.prototype.validate = function () {
        $('#packages .has-error').removeClass('has-error');

        try {
            // Validate required fields.
            var missingRequired = false;
            $('#packages .required').each(function () {
                if ($(this).val() == '' || $(this).val() == undefined) {
                    $(this).closest('.form-group').addClass('has-error');
                    missingRequired = true;
                }
            });
            if (missingRequired) {
                throw EALang.fields_are_required;
            }
            return true;
        } catch (message) {
            $('#providers .form-message')
                .addClass('alert-danger')
                .text(message)
                .show();
            return false;
        }
    };
    
    PackagesHelper.prototype.save = function (packageObj) {
        var postUrl = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_package';
        var postData = {
            csrfToken: GlobalVariables.csrfToken,
            packageObj: JSON.stringify(packageObj)
        };

        $.post(postUrl, postData, function (response) {
            if (!GeneralFunctions.handleAjaxExceptions(response)) {
                return;
            }

            Backend.displayNotification(EALang.package_saved);
            this.resetForm();
            $('#filter-packages .key').val('');
            this.filter('', response.id, true);
        }.bind(this), 'json').fail(GeneralFunctions.ajaxFailureHandler);
    };
    
    PackagesHelper.prototype.delete = function (id) {
        var postUrl = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_delete_package';
        var postData = {
            csrfToken: GlobalVariables.csrfToken,
            package_id: id
        };

        $.post(postUrl, postData, function (response) {
            if (!GeneralFunctions.handleAjaxExceptions(response)) {
                return;
            }
            Backend.displayNotification(EALang.package_deleted);
            this.resetForm();
            this.filter($('#filter-packages .key').val());
        }.bind(this), 'json').fail(GeneralFunctions.ajaxFailureHandler);
    };

    window.PackagesHelper = PackagesHelper;
})();