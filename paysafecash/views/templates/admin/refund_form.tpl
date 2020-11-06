<form id="module_form" class="defaultForm form-horizontal"
      action="index.php?controller=AdminModules&configure=paysafecash&tab_module=payments_gateways&module_name=paysafecash&token={$token}"
      method="post" enctype="multipart/form-data" novalidate="">
    <input type="hidden" name="refundPaysafecashModule" value="1">

    <div class="panel" id="fieldset_0">

        <div class="panel-heading">
            <i class="icon-cogs"></i> Einstellungen
        </div>

        <div class="form-wrapper">

            <div class="form-group">

                <label class="control-label col-lg-3">
                    Transaction ID
                </label>

                <div class="col-lg-3">
                    <input type="text" name="payment_id" id="payment_id"
                           value="pay_1000005846_9KuvUxMODaAR8Wf7LXc5GGbYMpSu9uBc_EUR" class="">

                    <p class="help-block"></p>
                </div>
            </div>

            <div class="form-group">

                <label class="control-label col-lg-3">
                    Amount
                </label>


                <div class="col-lg-3">

                    <input type="text" name="payment_amount" id="payment_amount"
                           value="0.10" class="">


                    <p class="help-block">


                    </p>

                </div>

            </div>

        </div><!-- /.form-wrapper -->

        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="refundPaysafecashModule"
                    class="btn btn-default pull-right">
                <i class="icon-AdminParentOrders"></i> Refund
            </button>
        </div>

    </div>


</form>