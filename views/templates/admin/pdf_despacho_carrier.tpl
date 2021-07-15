{foreach $orders as $item} 
<table style="width: 100%; margin:0px; padding:0px" cellpadding="0" cellspacing="0">
<tr>
	<td style="width: 50%; text-align: left;">
		{if $customers[$item->id]['url_code_qr']}
			<img src="{$customers[$item->id]['url_code_qr']}" style="width:70px; height:50px;  padding:0px;  margin:0px;" />
		{/if}
	</td>
	<td style="width: 50%; text-align: right;">
        {if $customers[$item->id]['url_code_barcode']}
		    <img src="{$customers[$item->id]['url_code_barcode']}" style="width:130px; height:50px;  padding:0px;  margin:0px;" />
        {/if}
	</td>
</tr>
</table>
<table>
    <tr>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%">
            <h4>Resumen</h4>
            <p style="line-height: 0.8; margin:0px;color: #333; font-size: 7px;"><b>Remitente:</b> {$customers[$item->id]['name']} <b>Destinatario:</b> {$customers[$item->id]['destiny']}</p>    
            <p style="line-height: 0.4;margin:0px; color: #333; font-size: 7px;"><b>Teléfono:</b> {$customers[$item->id]['phone']} <b> CI:</b> {$customers[$item->id]['ci']}</p>    
            <p style="line-height: 1.0;margin:0px;color: #333; font-size: 7px;"><b>Dirección:</b> {$customers[$item->id]['address']}</p>
			<p style="line-height: 1.0;margin:0px;color: #333; font-size: 7px;"><b>Contacto alternativo:</b> {$customers[$item->id]['alternative']} {$customers[$item->id]['alternative_phone']}  </p>
			
            {*<p style="color: #333; font-size: 9px;"></p>
            <p style="color: #333; font-size: 9px;"></p>*}
        </td>
        <td style="text-align:right; " width="25%">
            <h4>{$carrier}</h4>
            <p style="line-height: 0.3;font-size: 7px;margin:0px;color: #333"><b>Orden:</b> <i>{$item->id}</i></p>
            <p style="line-height: 0.3;font-size: 7px;margin:0px; color: #333"><b>Fecha:</b> <i>{*$genDate*}{*dateFormat date = $item->invoice_date full = 0*}{$item->invoice_date|date_format:'%Y-%m-%d'}</i></p>
        </td>
    </tr>
</table>  

<h4>Listado de productos</h4>
<table width="100%" cellpadding="0" cellspacing="0">
    {*<thead>
        <tr>
            <th style="background: #ffffb0; font-size: 8px; font-weigth:bold;" width="25%">C&oacute;digo</th>
            <th style="background: #ffffb0; font-size: 8px; font-weigth:bold;" width="65%">Nombre</th>
            <th style="background: #ffffb0; font-size: 8px; font-weigth:bold; text-align:center;" width="10%">Cantidad</th>
        </tr>
    </thead>*}
    <tbody>
       <tr>
            <th style="background: #ffffb0; font-size: 8px; font-weigth:bold;" width="25%">C&oacute;digo</th>
            <th style="background: #ffffb0; font-size: 8px; font-weigth:bold;" width="65%">Nombre</th>
            <th style="background: #ffffb0; font-size: 8px; font-weigth:bold; text-align:center;" width="10%">Cantidad</th>
        </tr>
        {foreach $products[$item->id] as $p}           
            <tr style="font-size: 7pt;">
                <td style="border:1px #666 solid; padding: 2px;" width="25%">{$p["product_reference"] } </td>
                <td style="border:1px #666 solid; padding: 2px;" width="65%">{$p["product_name"] } </td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;" width="10%">{$p["product_quantity"]}</td>
            </tr>
        {/foreach }
    </tbody>
</table>
<br/>
<br/>

<table style="font-size:6pt;width: 100%;">
	<tr>
		<td>
			<b>IMPORTANTE:</b> Ud. debe revisar junto con el distribuidor que las cantidades declaradas en la factura coincidan con la mercancía recibida.
            En caso de faltante, Ud. puede rechazar la entrega o anotar en las casillas siguientes lo productos. Estos productos serán repuestos a la mayor brevedad posible o, en caso de que el proveedor no tener existencias, el importe será devuelto al comprador.
            Una vez firmada y aceptada la factura, no se aceptan reclamaciones por faltante de producto.
		</td>
	</tr>
</table>

<table style="font-size:6pt;margin: 1px auto; width: 100%;">
	<tr>
		<td>
			<p style="font-weight:bold;">Productos Faltantes</p>
			<p>_______________________________</p>
			<p>_______________________________</p>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>
			<p style="font-weight:bold;">Cantidad</p>
			<p>____________</p>
			<p>____________</p>
		</td>
	</tr>
</table>

<table style="font-size:6pt;width: 100%; margin: 1px auto;">
	<tr>
		<td> <strong>IMPORTANTE:</strong> En caso de que esta orden sea el resultado de una acción fraudulenta de la persona que realizó la compra, la firma de este comprobante significa que Ud. está de acuerdo en que, tanto la Tienda como los Transportistas, tienen el derecho de retirar los bienes relacionados en este comprobante.<br/>
            Será potestad única de la Tienda la determinación del carácter fraudulento de una orden y, a tal efecto, emitirá un documento que el transportista exhibirá en el momento de retirar los bienes. La Tienda se reserva el derecho de iniciar las acciones legales pertinentes contra la persona que efectuó la compra.
       </td>
	</tr>
</table>

<table style="font-size:6pt;margin: 1px auto; width: 100%;">
	<tr>
		<td>
			<p style="font-weight: bold;">Entrega:</p>
			<p>Nombre: __________________________</p>
			<p>Firma: ______________ </p>
			<p>Fecha: ____________</p>
		</td>
		<td style="width: 70px;">&nbsp;</td>
		<td>
			<p style="font-weight: bold;">Recibe:</p>
			<p>Nombre: ___________________________</p>
			<p>CI: ___________________________</p>
			<p>Firma: ______________ </p>
			<p>Fecha: ____________</p>
		</td>
	</tr>
</table>
</tr>
</table>
{/foreach}

