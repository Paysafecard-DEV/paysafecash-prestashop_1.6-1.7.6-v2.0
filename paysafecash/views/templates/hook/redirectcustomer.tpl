<div>
    {if $payed}
        {l s='Thank you, your order has been paid for successfully' mod='paysafecash'}
    {/if}
    {if $topay}
        {l s='Please go to the nearest shop to pay for your order.' mod='paysafecash'}
    {/if}
</div>