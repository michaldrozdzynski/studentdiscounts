{extends file='layouts/layout-full-width.tpl'}

{block name='content'}
  <section id="main">
    <header class="page-header">
      <h1>{l s='Your student account' mod='studentdiscounts'}</h1>
    </header>
    <div class="row ">
        <label class="col-md-3">
                        {l s='First name and last name:' mod='studentdiscounts'}
                    </label>
        <div class="col-md-6">
          {$studentaccount['name']}
        </div>
    </div>
    <div class="row ">
        <label class="col-md-3">
                        {l s='Email:' mod='studentdiscounts'}
                    </label>
        <div class="col-md-6">
          {$studentaccount['email']}
        </div>
    </div>  
    <div class="row ">
        <label class="col-md-3">
                        {l s='Has the email been verified?' mod='studentdiscounts'}
                    </label>
        <div class="col-md-6">
          {$studentaccount['verificated']}
        </div>
    </div>
    <div class="row ">
        <label class="col-md-3">
                        {l s='Has the student domain been confirmed?' mod='studentdiscounts'}
                    </label>
        <div class="col-md-6">
          {$studentaccount['validated']}
        </div>
    </div>
    <div class="row ">
        <label class="col-md-3">
                        {l s='Is the account active?' mod='studentdiscounts'}
                    </label>
        <div class="col-md-6">
          {$studentaccount['active']}
        </div>
    </div> 

    {if count($studentaccount['studentCart']) == 0}
    <form action="{Context::getContext()->link->getModuleLink('studentdiscounts', 'studentaccount')}" enctype="multipart/form-data" method="post">
    <div class="row ">
    <label class="col-md-3">
                        {l s='To activate your account, please send a photo of your student ID.' mod='studentdiscounts'}
                    </label>
    <div class="col-md-6"> 
    <input type="file" multiple="multiple" id="studentCart" name="studentCart[]">
  </div>
  </div>
            <footer class="form-footer clearfix">
            <input type="hidden" name="submitCreate" value="1">            
            <input class="btn btn-primary form-control-submit float-xs-right" value="{l s='Save' mod='studentdiscounts'}"type="submit"/>
        </footer>
    </form>
    {elseif count($studentaccount['studentCart']) > 0}
    <div class="row">
        <label class="col-md-3">
                        {l s='Photos of student ID card sent.' mod='studentdiscounts'}
                    </label>
      <div class="col-md-6"> 
        {if $studentaccount['active'] = 0}
          {l s='Wait for verification' mod='studentdiscounts'}
        {/if}
      </div>
        </div>
        <div class="row">
          <div class="col-md-3" width="50%" style="text-align:center; margin:auto;">
            <a href="{Context::getContext()->link->getModuleLink('studentdiscounts', 'studentaccount', array('deletePhotos' => true))}"><button type="button" class="btn btn-danger">{l s='Delete photos' mod='studentdiscounts'}</button></a>
          </div>
          <div class="col-md-6">
            {foreach $studentaccount['images'] as $image}
              <a href="{_MODULE_DIR_ }studentdiscounts/upload/studentcarts/{$image}" target="_blank"><img width="200px" src="{_MODULE_DIR_ }studentdiscounts/upload/studentcarts/{$image}" alt="student cart"/></a>
            {/foreach}
          </div>
        </div>


    {/if}
  </section>
  <script>
    const input = document.querySelector('#studentCart');

    // Listen for files selection
    input.addEventListener('change', (e) => {
        // Retrieve all files
        const files = input.files;

        // Check files count
        if (files.length > 2) {
            alert("{l s='Only 2 files are allowed to upload' mod='studentdiscounts'}");
            input.value = '';
        }
    });
  </script>
{/block}