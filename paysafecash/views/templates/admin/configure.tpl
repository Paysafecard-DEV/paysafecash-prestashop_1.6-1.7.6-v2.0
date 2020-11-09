
<div class="tab-content">
	<div role="tabpanel active" class="tab-pane active" id="config">
		<div class="panel">
			<div class=paysafecashh-content">
				<div class="row">
					<div class="col-md-8">
						<img src="/modules/paysafecash/logo_long.png" width="173px" height="50px" alt="Paysafecash Logo" /><br>
						<strong>Paysafecash brings cash online</strong><p>Paysafecash is a new, alternative payment method that makes it possible to pay securely and easily with cash on the Internet. Even people who do not have any access to credit cards or online banking or do not want to disclose their financial details online, can use Paysafecash for their online purchases. Paysafecash has been specifically developed for the requirements of customers from the eCommerce sector, such as travel, ticketing and retail.</p><p>Using Paysafecash, products or services can be ordered online and then paid for offline by simply using cash at the next partner payment point.</p>
						Offering Paysafecash payments pays off:<li>Opens up access to untouched customer groups</li><li>Incremental revenue with cash paying customers</li><li>100% payment guarantee, no risk of chargeback</li><li>Guarantees a secure and customer-friendly payment process</li><li>No cannibalization effect of existing payment methods</li> <br>
					</div>
				</div>
			</div>
	</div>

</div>
		<div style="background-color: #eaebec; padding: 20px;">
			<ul class="nav nav-tabs" id="myTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#config_view" role="tab">Config</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#refund_view" role="tab">Refund</a>
				</li>

			</ul>
			<div class="tab-content">
                <div class="tab-pane active" id="config_view" role="tabpanel">{include file='./config_form.tpl'}</div>
                <div class="tab-pane" id="refund_view" role="tabpanel">{include file='./refund_form.tpl'}</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function(){
					$('#myTab a').click(function (e) {
						e.preventDefault()
						$(this).tab('show')
                        console.log($(this));
					});
				});
			</script>
		</div>
