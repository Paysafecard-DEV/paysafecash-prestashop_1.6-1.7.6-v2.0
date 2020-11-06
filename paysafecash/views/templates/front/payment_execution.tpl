{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='paysafecash'}">{l s='Checkout' mod='paysafecash'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Paysafecash payment' mod='paysafecash'}
{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='paysafecash'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='paysafecash'}</p>
{else}

<h3>{l s='Paysafecash Payment' mod='paysafecash'}</h3>
<form action="{$link->getModuleLink('paysafecash', 'redirect', [], true)|escape:'html'}" method="post">
<p>
	{l s='Here is a short summary of your order:' mod='paysafecash'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='paysafecash'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='paysafecash'}
    {/if}
</p>
	<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
<p>
	<b>{l s='Please confirm your order by clicking "I confirm my order".' mod='paysafecash'}</b>
</p>
<p class="cart_navigation" id="cart_navigation">
	<input type="submit" value="{l s='I confirm my order' mod='paysafecash'}" class="exclusive_large" />
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='paysafecash'}</a>
</p>
</form>
{/if}
