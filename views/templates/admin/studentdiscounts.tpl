   
    <div class="panel">
    	<h3><i class="icon icon-image"></i>{l s='Student ID' mod='studentdiscounts'}</h3>
         {if isset($studentCart[0])}
        <div class="row">
        <div class="col-lg-4">
            <img src="{_MODULE_DIR_ }studentdiscounts/upload/studentcarts/{$studentCart[0]}" class="img-thumbnail" width="400" />
        </div>
        {if isset($studentCart[1])}
          <div class="col-lg-4">
            <img src="{_MODULE_DIR_ }studentdiscounts/upload/studentcarts/{$studentCart[1]}" class="img-thumbnail" width="400" />
        </div>
        {/if}
        </div>
        <div style="text-align: center">
            <a href="{Context::getContext()->link->getAdminLink('AdminModules', false)}&configure=studentdiscounts&activeStudentAccount=1&studentId={$studentId}&token={Tools::getAdminTokenLite('AdminModules')}">
            <button type="button" style="padding: 20px; font-size: 20px;" class="btn btn-success">{l s='Activate the student account' mod='studentdiscounts'}</button>
            </a>
             <a href="{Context::getContext()->link->getAdminLink('AdminModules', false)}&configure=studentdiscounts&activeStudentAccount=0&studentId={$studentId}&token={Tools::getAdminTokenLite('AdminModules')}">
            <button type="button" style="padding: 20px; font-size: 20px;" class="btn btn-danger">{l s='Don\'t activate the account' mod='studentdiscounts'}</button>
            </a>

        </div>
        {else}
        {l s='The student has not yet sent a photo of the student ID.' mod='studentdiscounts'}
         {/if}
                             <div class="panel-footer">
			<a href="{Context::getContext()->link->getAdminLink('AdminModules', false)}&configure=studentdiscounts&token={Tools::getAdminTokenLite('AdminModules')}">
            <button type="button" class="btn btn-default pull-right">
				<i class="process-icon-back"></i>{l s='Back' mod='studentdiscounts'}
			</button></a>
		</div>
    </div>
   

