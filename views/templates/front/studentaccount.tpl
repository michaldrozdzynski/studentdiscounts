{extends file='layouts/layout-full-width.tpl'}

{block name='content'}
  <section id="main">
    <header class="page-header">
      <h1>Twoje konto studenckie</h1>
    </header>
    <div class="row ">
        <label class="col-md-3">
                        Imię i nazwisko:
                    </label>
        <div class="col-md-6">
          {$studentaccount['name']}
        </div>
    </div>
    <div class="row ">
        <label class="col-md-3">
                        E-mail:
                    </label>
        <div class="col-md-6">
          {$studentaccount['email']}
        </div>
    </div>  
    <div class="row ">
        <label class="col-md-3">
                        Czy zweryfikowano email?
                    </label>
        <div class="col-md-6">
          {$studentaccount['verificated']}
        </div>
    </div>
    <div class="row ">
        <label class="col-md-3">
                        Czy potwierdzono domene studencką?
                    </label>
        <div class="col-md-6">
          {$studentaccount['validated']}
        </div>
    </div>
    <div class="row ">
        <label class="col-md-3">
                        Czy konto jest aktywne?
                    </label>
        <div class="col-md-6">
          {$studentaccount['active']}
        </div>
    </div> 

    {if $studentaccount['activeValue'] == 0 && count($studentaccount['studentCart']) == 0}
    <form action="{Context::getContext()->link->getModuleLink('studentdiscounts', 'studentaccount')}" enctype="multipart/form-data" method="post">
    <div class="row ">
    <label class="col-md-3">
                        Aby aktywować konto prześli zdjęcie legitymacji.
                    </label>
    <div class="col-md-6"> 
    <input type="file" multiple="multiple" id="studentCart" name="studentCart[]">
  </div>
  </div>
            <footer class="form-footer clearfix">
            <input type="hidden" name="submitCreate" value="1">
            
            <input class="btn btn-primary form-control-submit float-xs-right" value="Save"type="submit"/>

            
        </footer>
    </form>
    {elseif count($studentaccount['studentCart']) > 0 && $studentaccount['activeValue'] == 0 }
    <div class="row">
        <label class="col-md-3">
                        Przesłano zdjęcia legitymacji.
                    </label>
      <div class="col-md-6"> 
        Czekaj na weryfikację
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
            alert(`Only 2 files are allowed to upload.`);
            input.value = '';
        }
    });
  </script>
{/block}