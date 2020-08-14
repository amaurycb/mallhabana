<form id="configuration_form" class="defaultForm form-horizontal mallhabana" action="" method="post" enctype="multipart/form-data" novalidate="" autocomplete="off">
	
	<div class="panel" id="fieldset_0">
		<div class="panel-heading">
			Despacho por Proveedor
		</div>
		<div class="form-wrapper">											
			<div class="form-group">
				<label class="control-label col-lg-3 required">Proveedor</label>
				<div class="col-lg-9">
					<select name="provider">
						{foreach $suppliers as $supplier}
							<option value="{$supplier->id_supplier}">{$supplier->name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3 required">Desde</label>
				<div class="col-lg-9">
				<input id="start_date" type="text" data-hex="true" class="datepicker" name="start_date" min="2020-08-09" max="{date('Y-m-d')}">
				</div>
			</div>		
			<div class="form-group">
				<label class="control-label col-lg-3 required">Hasta</label>
				<div class="col-lg-9">
				<input id="end_date" type="text" data-hex="true" class="datepicker" name="end_date" min="2020-08-09" max="{date('Y-m-d')}">
				</div>
			</div>																
		</div><!-- /.form-wrapper -->						
					
		<div class="panel-footer">
			<button type="submit" value="1" id="configuration_form_submit_btn" name="submitDespacho" class="btn btn-default pull-right">
				<i class="process-icon-download"></i> Generar
			</button>				
		</div>
	</div>
</form>
<script>
$("#start_date").datepicker({
	dateFormat: 'yy-mm-dd',
	minDate: new Date('2020-08-09'),
	maxDate: 0
});

$("#end_date").datepicker({
	dateFormat: 'yy-mm-dd',
	minDate: new Date('2020-08-09'),
	maxDate: 0
});
</script>