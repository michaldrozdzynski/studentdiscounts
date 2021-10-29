<div class="panel col-lg-12">
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body" id="modal-text">
        
      </div>
      <div class="modal-footer" id="modal-footer">
      </div>
    </div>
  </div>
</div>

    <table class="table table-striped">
    <thead>
        <tr>
            <th class="col">ID</th>
            <th class="col">Email</th>
            <th class="col"></th>
            <th class="col"></th>
        </tr>
        
    </thead>
    <tbody>
{foreach $students as $student}
    {assign var="confirm" value= Context::getContext()->link->getAdminLink('AdminModules')}
    {assign var="delete" value= Context::getContext()->link->getAdminLink('AdminModules')}
    
	<tr>
		<td class="row">{$student.id_studentdiscounts}</td>
		<td>{$student.email}</td>
		<td><a><button onClick="modalValue('{$student.email}', {$student.id_studentdiscounts})" data-toggle="modal" data-target="#exampleModal" type="button" class="btn btn-primary">ZATWIERDŹ</button></a></td>
        <td><a href="{Context::getContext()->link->getAdminLink('AdminModules')}&configure=studentdiscounts&id={$student.id_studentdiscounts}&del=delete"><button onClick="showModal('{$student.email}' )" type="button" class="btn btn-danger">USUŃ</button></a></td>
	</tr>
{/foreach}
    </tbody>
</table>
<div>
<script>
  function modalValue(email, id) {
    var link = "{Context::getContext()->link->getAdminLink('AdminModules')}" + "&configure=studentdiscounts&id="+ id + "&confirm=confirm";
    var domain = email.split('@')[1];
    var text = "Czy chcesz zatwiedzić wszystkich klientów z domeny <strong>" + domain + "</strong>?"
    $('#modal-text').html(text);
    var buttons = '<a href="' + link + '"><button type="button" class="btn btn-danger">NIE, ZATWIERDŹ TYLKO UŻYTKOWNIKA</button></a>' +
    '<a href="' + link + '&domain=' + domain + '"><button type="button" class="btn btn-primary">DODAJ WSZYSTKICH UŻYTKOWNIKÓW Z TEJ DOMENY</button></a>';
    $('#modal-footer').html(buttons);
  }
</script>