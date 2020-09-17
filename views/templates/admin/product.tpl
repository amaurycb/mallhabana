<form id="configuration_form" class="defaultForm form-horizontal mallhabana" method="post" enctype="multipart/form-data" novalidate="">
	<input type="hidden" name="id_product" value="{$product->id}">
	<div class="panel" id="fieldset_0">
		<div class="panel-heading"> Actualizar Cantidad</div>
		<div class="form-wrapper">											
			<div class="form-group">
				<label class="control-label col-lg-3 required">Cantidad</label>
				<div class="col-lg-9">
                    <input name="qty" type="number" step="1" class="form-control" required="required" value="{$sa}"/>
					<i>Cantidad disponible: {$sa}</i>
				</div>
			</div>											
		</div><!-- /.form-wrapper -->						
					
		<div class="panel-footer">
			<button type="submit" value="1" id="configuration_form_submit_btn" name="submitUpdateQty" class="btn btn-default pull-right">
				<i class="process-icon-save"></i> Guardar
			</button>					
		</div>
	</div>
</form>