
{if (isset($status) == true) && ($status == 'ok')}
<h3>{l s='Your order is complete.' mod='paysafecash'}</h3>
<p>
	<br />- {l s='Amount' mod='paysafecash'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br />- {l s='Reference' mod='paysafecash'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='paysafecash'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='paysafecash'}</a>
</p>
{else}
<h3>{l s='Your orderhas not been accepted.' sprintf=$shop_name mod='paysafecash'}</h3>
<p>
	<br />- {l s='Reference' mod='paysafecash'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Please, try to order again.' mod='paysafecash'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='paysafecash'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='paysafecash'}</a>
</p>
{/if}
<hr />