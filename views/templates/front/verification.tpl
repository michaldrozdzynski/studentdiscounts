{extends file='layouts/layout-full-width.tpl'}

{block name='content'}
  <section id="main">
  <h1>
   {l s='Your e-mail has been successfully verified!' mod='studentdiscounts'}
  </h1>
  <p>
    {$verificationText}
  </p>
  {if Context::getContext()->customer->id == null}
  <a href="{$urls.pages.my_account}">Kliknij by zalogować się</a>
  {else}
    <a href="{Context::getContext()->link->getModuleLink('studentdiscounts', 'studentaccount')}">Kliknij by wejść w ustawienia konta studenckiego</a>
  {/if}
  </section>
{/block}