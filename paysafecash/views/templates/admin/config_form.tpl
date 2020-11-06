<form id="module_form" class="defaultForm form-horizontal"
      action="index.php?controller=AdminModules&configure=paysafecash&tab_module=payments_gateways&module_name=paysafecash&token={$token}"
      method="post" enctype="multipart/form-data" novalidate="">
    <input type="hidden" name="submitPaysafecashModule" value="1">

    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-cogs"></i> Einstellungen
        </div>


        <div class="form-wrapper">

            <div class="form-group">

                <label class="control-label col-lg-3">
                    Testmodus
                </label>


                <div class="col-lg-9">

																	<span class="switch prestashop-switch fixed-width-lg">
																				<input type="radio"
                                                                                       name="PAYSAFECASH_TEST_MODE"
                                                                                       id="PAYSAFECASH_TEST_MODE_on"
                                                                                       value="1" {if $fields_value.PAYSAFECASH_TEST_MODE == true} checked="checked"{/if}>
										<label for="PAYSAFECASH_TEST_MODE_on">Ja</label>
																				<input type="radio"
                                                                                       name="PAYSAFECASH_TEST_MODE"
                                                                                       id="PAYSAFECASH_TEST_MODE_off"
                                                                                       value="" {if $fields_value.PAYSAFECASH_TEST_MODE == false} checked="checked"{/if}>
										<label for="PAYSAFECASH_TEST_MODE_off">Nein</label>
																				<a class="slide-button btn"></a>
									</span>


                    <p class="help-block">
                        Wenn der Testmodus aktiviert ist, machen Sie Transaktionen in dem Paysafecash Testsystem.
                        Deswegen ist es notwendig, den Testsystem API Key anzugeben.
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
                        Dieser Key wird Ihnen von dem Paysafecash Support übermittelt. Es gibt einen Key für das
                        Testsystem, und einen Key für das Produktivsystem.
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
                        Dieser Key wird Ihnen von dem Paysafecash Support übermittelt. Es gibt einen Key für das
                        Testsystem, und einen Key für das Produktivsystem.
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
                        Dieses Feld spezifiziert das verwendete Reporting Criteria. Sie können diesen Paramenter dazu
                        verwenden, Transaktionen von unterschiedlichen Shops/URLs zu unterscheiden. Benützen Sie dieses
                        Feld nur, wenn Sie sich zuvor mit dem Paysafecash Support Team abgesprochen haben. Dieser
                        Parameter muss auch im Paysafecash System konfiguriert sein.
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
										<label for="PAYSAFECASH_DATA_TAKEOVER_MODE_on">Ja</label>
																				<input type="radio"
                                                                                       name="PAYSAFECASH_DATA_TAKEOVER_MODE"
                                                                                       id="PAYSAFECASH_DATA_TAKEOVER_MODE_off"
                                                                                       value="" {if $fields_value.PAYSAFECASH_DATA_TAKEOVER_MODE == false} checked="checked"{/if}>
										<label for="PAYSAFECASH_DATA_TAKEOVER_MODE_off">Nein</label>
																				<a class="slide-button btn"></a>
									</span>


                    <p class="help-block">
                        Bietet die Möglichkeit, Kundendaten während der Transaktionserstellung mitzuschicken, damit das
                        Paysafecash Registrierungsformular vorausgefüllt ist. Dies erleichtert die Registrierung der
                        Kunden.
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
                        Das Zeitfenster für den Kunden um zu einem Shop zu gehen und für die Transaktion zu zahlen.
                        Minimum: 1 Tag - Maximum: 14 Tage
                    </p>

                </div>

            </div>


            <div class="form-group">

                <label class="control-label col-lg-3">
                    Debug Modus
                </label>


                <div class="col-lg-9">

																	<span class="switch prestashop-switch fixed-width-lg">
																				<input type="radio"
                                                                                       name="PAYSAFECASH_DEBUG"
                                                                                       id="PAYSAFECASH_DEBUG_on"
                                                                                       value="1" {if $fields_value.PAYSAFECASH_DEBUG == true} checked="checked"{/if}>
										<label for="PAYSAFECASH_DEBUG_on">Ja</label>
																				<input type="radio"
                                                                                       name="PAYSAFECASH_DEBUG"
                                                                                       id="PAYSAFECASH_DEBUG_off"
                                                                                       value="" {if $fields_value.PAYSAFECASH_DEBUG == false} checked="checked"{/if}>
										<label for="PAYSAFECASH_DEBUG_off">Nein</label>
																				<a class="slide-button btn"></a>
									</span>


                    <p class="help-block">
                        Wenn der Debug Modus aktiviert ist
                    </p>

                </div>

            </div>


        </div><!-- /.form-wrapper -->


        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitPaysafecashModule"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Speichern
            </button>
        </div>

    </div>


</form>