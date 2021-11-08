    {if isset($studentCart[0])}
    <div class="panel">
    	<h3><i class="icon icon-image"></i> Student Cart</h3>
        <div class="row">
        <div class="col-lg-4">
            <img src="/presta/modules/studentdiscounts/upload/studentcarts/{$studentCart[0]}" class="img-thumbnail" width="400" />
        </div>
        {if isset($studentCart[1])}
          <div class="col-lg-4">
            <img src="/presta/modules/studentdiscounts/upload/studentcarts/{$studentCart[1]}" class="img-thumbnail" width="400" />
        </div>
        {/if}
        </div>
    </div>
    {/if}

