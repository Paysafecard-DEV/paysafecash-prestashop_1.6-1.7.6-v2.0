<form id="module_form" class="defaultForm form-horizontal"
      action="index.php?controller=AdminModules&configure=paysafecash&tab_module=payments_gateways&module_name=paysafecash&token={$token}"
      method="post" enctype="multipart/form-data" novalidate="">
    <input type="hidden" name="submitPaysafecashModule" value="1">

    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i> Settings
        </div>

        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    Test mode
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio"
                               name="PAYSAFECASH_TEST_MODE"
                               id="PAYSAFECASH_TEST_MODE_on"
                               value="1" {if $fields_value.PAYSAFECASH_TEST_MODE == true} checked="checked"{/if}>
                        <label for="PAYSAFECASH_TEST_MODE_on">Enabled</label>
                        <input type="radio"
                               name="PAYSAFECASH_TEST_MODE"
                               id="PAYSAFECASH_TEST_MODE_off"
                               value="" {if $fields_value.PAYSAFECASH_TEST_MODE == false} checked="checked"{/if}>
                        <label for="PAYSAFECASH_TEST_MODE_off">Disabled</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">
                        {l s='If the test mode is enabled you are making transactions against paysafecash test environment. Therefore the test environment API key is necessary to be set.' mod='paysafecash'}
                    </p>
                </div>
            </div>

            <div class="form-group">

                <label class="control-label col-lg-3">
                    API Key
                </label>

                <div class="col-lg-3">

                    <div class="input-group fixed-width-lg">
                        <span class="input-group-addon">
                            <i class="icon-key"></i>
                        </span>
                        <input type="password" id="PAYSAFECASH_API_KEY" name="PAYSAFECASH_API_KEY" class=""
                               value="{$fields_value.PAYSAFECASH_API_KEY}">
                    </div>

                    <p class="help-block">
                        {l s='This key is provided by the paysafecash support team. There is one key for the test- and one for production environment.' mod='paysafecash'}
                    </p>
                </div>
            </div>

            <div class="form-group">

                <label class="control-label col-lg-3">
                    Webhook Key
                </label>

                <div class="col-lg-3">

                    <div class="input-group fixed-width-lg">
                        <span class="input-group-addon">
                            <i class="icon-key"></i>
                        </span>
                        <input type="password" id="PAYSAFECASH_WEBHOOK_KEY" name="PAYSAFECASH_WEBHOOK_KEY" class=""
                               value="{$fields_value.PAYSAFECASH_WEBHOOK_KEY}">
                    </div>

                    <p class="help-block">
                        {l s='This key is provided by the paysafecash support team. There is one key for the test- and one for production environment.' mod='paysafecash'}
                    </p>
                </div>
            </div>

            <div class="form-group">

                <label class="control-label col-lg-3">
                    Submerchant ID
                </label>

                <div class="col-lg-3">

                    <input type="text" name="PAYSAFECASH_SUBMERCHANT_ID" id="PAYSAFECASH_SUBMERCHANT_ID"
                           value="{$fields_value.PAYSAFECASH_SUBMERCHANT_ID}" class="">
                    <p class="help-block">
                        {l s='This field specifies the used Reporting Criteria. You can use this parameter to distinguish your transactions per brand/URL. Use this field only if agreed beforehand with the paysafecash support team. The value has to be configured in both systems.' mod='paysafecash'}
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    Customer Data Takeover
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio"
                               name="PAYSAFECASH_DATA_TAKEOVER_MODE"
                               id="PAYSAFECASH_DATA_TAKEOVER_MODE_on"
                               value="1" {if $fields_value.PAYSAFECASH_DATA_TAKEOVER_MODE == true} checked="checked"{/if}>
                        <label for="PAYSAFECASH_DATA_TAKEOVER_MODE_on">Enabled</label>
                        <input type="radio"
                               name="PAYSAFECASH_DATA_TAKEOVER_MODE"
                               id="PAYSAFECASH_DATA_TAKEOVER_MODE_off"
                               value="" {if $fields_value.PAYSAFECASH_DATA_TAKEOVER_MODE == false} checked="checked"{/if}>
                        <label for="PAYSAFECASH_DATA_TAKEOVER_MODE_off">Disabled</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">
                        {l s='Provides the possibility to send customer data during the payment creation, so the Paysafecash registration form is prefilled. This has the sole purpose to make the registration of the customer easier.' mod='paysafecash'}
                    </p>
                </div>
            </div>
            <div class="form-group">

                <label class="control-label col-lg-3">
                    Variable Transaction Timeout
                </label>
                <div class="col-lg-3">
                    <input type="text" name="PAYSAFECASH_VARIABLE_TIMEOUT" id="PAYSAFECASH_VARIABLE_TIMEOUT"
                           value="{$fields_value.PAYSAFECASH_VARIABLE_TIMEOUT}" class="">
                    <p class="help-block">
                        {l s='The time frame the customer is given to go to a payment point and pay for the transaction. Minimum: 1 day – Maximum: 14 days' mod='paysafecash'}
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    Debug Mode
                </label>
                <div class="col-lg-9">

                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio"
                               name="PAYSAFECASH_DEBUG"
                               id="PAYSAFECASH_DEBUG_on"
                               value="1" {if $fields_value.PAYSAFECASH_DEBUG == true} checked="checked"{/if}>
                        <label for="PAYSAFECASH_DEBUG_on">Enabled</label>
                        <input type="radio"
                               name="PAYSAFECASH_DEBUG"
                               id="PAYSAFECASH_DEBUG_off"
                               value="" {if $fields_value.PAYSAFECASH_DEBUG == false} checked="checked"{/if}>
                        <label for="PAYSAFECASH_DEBUG_off">Disabled</label>
                        <a class="slide-button btn"></a>
                    </span>

                    <p class="help-block">
                        {l s='Enabled debug mode writes logs into the log-file' mod='paysafecash'}
                    </p>
                </div>
            </div>
        </div><!-- /.form-wrapper -->

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitPaysafecashModule"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Save
            </button>
        </div>
    </div>
</form>