<section>
  <p>{l s='Please send us your check following these rules:' d='Modules.Checkpayment.Shop'}
    <dl>
      <dt>{l s='Amount' d='Modules.Checkpayment.Shop'}</dt>
      <dd>{$checkTotal}</dd>
      <dt>{l s='Payee' d='Modules.Checkpayment.Shop'}</dt>
      <dd>{$checkOrder}</dd>
      <dt>{l s='Send your check to this address' d='Modules.Checkpayment.Shop'}</dt>
      <dd>{$checkAddress nofilter}</dd>
    </dl>
  </p>
</section>
