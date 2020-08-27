<script src="<?= asset_url('assets/js/backend_packages.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_packages_helper.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken      : <?= json_encode($this->security->get_csrf_hash()) ?>,
        baseUrl        : <?= json_encode($base_url) ?>,
        dateFormat     : <?= json_encode($date_format) ?>,
        timeFormat     : <?= json_encode($time_format) ?>,
        packages       : <?= json_encode($packages) ?>,
        services       : <?= json_encode($services) ?>,
        user           : {
            id         : <?= $user_id ?>,
            email      : <?= json_encode($user_email) ?>,
            role_slug  : <?= json_encode($role_slug) ?>,
            privileges : <?= json_encode($privileges) ?>
        }
    };

    $(document).ready(function() {
        BackendPackages.initialize(true);
    });
</script>
<div id="packages-page" class="container-fluid backend-page">
    
    <div class="tab-content">

        <!-- PROVIDERS TAB -->

        <div role="tabpanel" class="tab-pane active" id="packages">
            <div class="row">
                <div id="filter-packages" class="filter-records column col-xs-12 col-sm-5">
                    <form>
                        <div class="input-group">
                            <input type="text" class="key form-control">

                            <span class="input-group-addon">
                        <div>
                            <button class="filter btn btn-default" type="submit" title="<?= lang('filter') ?>">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                            <button class="clear btn btn-default" type="button" title="<?= lang('clear') ?>">
                                <span class="glyphicon glyphicon-repeat"></span>
                            </button>
                        </div>
                    </span>
                        </div>
                    </form>

                    <h3><?= lang('packages') ?></h3>
                    <div class="results"></div>
                </div>

                <div class="record-details column col-xs-12 col-sm-7">
                    <div class="pull-left">
                        <div class="add-edit-delete-group btn-group">
                            <button id="add-package" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span>
                                <?= lang('add') ?>
                            </button>
                            <button id="edit-package" class="btn btn-default" disabled="disabled">
                                <span class="glyphicon glyphicon-pencil"></span>
                                <?= lang('edit') ?>
                            </button>
                            <button id="delete-package" class="btn btn-default" disabled="disabled">
                                <span class="glyphicon glyphicon-remove"></span>
                                <?= lang('delete') ?>
                            </button>
                        </div>

                        <div class="save-cancel-group btn-group" style="display:none;">
                            <button id="save-package" class="btn btn-primary">
                                <span class="glyphicon glyphicon-ok"></span>
                                <?= lang('save') ?>
                            </button>
                            <button id="cancel-package" class="btn btn-default">
                                <span class="glyphicon glyphicon-ban-circle"></span>
                                <?= lang('cancel') ?>
                            </button>
                        </div>
                    </div>

                    <?php // This form message is outside the details view, so that it can be
                    // visible when the user has working plan view active. ?>
                    <div class="form-message alert" style="display:none;"></div>
                    <br>
                    <div class="details-view package-view">
                        <h3><?= lang('details') ?></h3>

                        <input type="hidden" id="package-id" class="record-id">

                        <div class="row">
                            <div class="package-details col-xs-12 col-sm-6">
                                <div class="form-group">
                                    <label for="package-name"><?= lang('name') ?> *</label>
                                    <input id="package-name" class="form-control required" maxlength="256">
                                </div>
                                <div class="form-group">
                                    <label for="package-price"><?= lang('price') ?> *</label>
                                    <input id="package-price" class="form-control required">
                                </div>
                                <div class="form-group">
                                    <label for="package-class-no"><?= lang('class_no') ?> *</label>
                                    <input id="package-class-no" class="form-control required">
                                </div>
                                <div class="form-group">
                                    <label for="package-description"><?= lang('description') ?> *</label>
                                    <textarea id="package-description" class="form-control required" rows="3" ></textarea>
                                </div>
                                
                            </div>
                            <div class="package-settings col-xs-12 col-sm-6">
                                <h4><?= lang('services') ?></h4>
                                <div id="package-services" class="well" ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
