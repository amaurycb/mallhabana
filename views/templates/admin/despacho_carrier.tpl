<form id="configuration_form" class="defaultForm form-horizontal mallhabana" action="" method="post" enctype="multipart/form-data" novalidate=""  autocomplete="off">
	
	<div class="panel" id="fieldset_0">
		<div class="panel-heading">
			Despacho por Transportista
		</div>
		<div class="form-wrapper">											
			<div class="form-group">
				<label class="control-label col-lg-3 required">Transportista</label>
				<div class="col-lg-9">
					<select name="carrier">
						{foreach $carriers as $carrier}
							<option value="{$carrier->id}">{$carrier->name}</option>
						{/foreach}
					</select>
				</div>
			</div>
				
			<div class="form-group">
				<label class="control-label col-lg-3 required">Fecha</label>
				<div class="col-lg-9">
				<input id="date_query" type="text" data-hex="true" class="datepicker" name="date_query" min="2020-08-09" max="{date('Y-m-d')}">
				</div>
			</div>															
		</div><!-- /.form-wrapper -->						
					
		<div class="panel-footer">
			<button type="submit" value="1" id="configuration_form_submit_btn" name="submitDespachoCarrier" class="btn btn-default pull-right">
				<i class="process-icon-download"></i> Generar
			</button>					
		</div>
	</div>
</form>
<script>
$("#date_query").datepicker({
	dateFormat: 'yy-mm-dd',
	minDate: new Date('2020-08-09'),
	maxDate: 0
});
</script>