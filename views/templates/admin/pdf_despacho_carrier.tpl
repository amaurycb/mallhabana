{foreach $orders as $item} 
<table style="width: 100%">
<tr>
	<td style="width: 50%; text-align: left;">
		{if $customers[$item->id]['url_code_qr']}
			<img src="{$customers[$item->id]['url_code_qr']}" style="width:70px; height:70px;  padding:0px;" />
		{/if}
	</td>
	<td style="width: 50%; text-align: right;">
        {if $customers[$item->id]['url_code_barcode']}
		    <img src="{$customers[$item->id]['url_code_barcode']}" style="width:130px; height:65px;  padding:0px;" />
        {/if}
	</td>
</tr>
</table>
<table>
    <tr>
<table>
    <tr>
        <td>
            <h2>Resumen</h2>
            <p style="color: #333; font-size: 11px;"><b>Remitente:</b> {$customers[$item->id]['name']}</p>    
            <p style="color: #333; font-size: 11px;"><b>Destinatario:</b> {$customers[$item->id]['destiny']}</p>    
            <p style="color: #333; font-size: 11px;"><b>Dirección:</b> {$customers[$item->id]['address']}</p>    
            <p style="color: #333; font-size: 11px;"><b>Teléfono:</b> {$customers[$item->id]['phone']}</p>
        </td>
        <td style="text-align:right; ">
            <h2>{$carrier}</h2>
            <h3 style="color: #333">Orden: <i>{$item->id}</i></h3>
            <h3 style="color: #333">Fecha: <i>{$genDate}</i></h3>
        </td>
    </tr>
</table>  
<br/>
<br/>

<h3>Listado de productos</h3>
<table>
    <thead>
        <tr>
            <th style="background: #ffffb0; font-size: 13px; font-weigth:bold;">C&oacute;digo</th>
            <th style="background: #ffffb0; font-size: 13px; font-weigth:bold;">Nombre</th>
            <th style="background: #ffffb0; font-size: 13px; font-weigth:bold; text-align:center;">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        {foreach $products[$item->id] as $p}           
            <tr style="font-size: 10pt;">
                <td style="border:1px #666 solid; padding: 2px;">{$p["product_reference"] } </td>
                <td style="border:1px #666 solid; padding: 2px;">{$p["product_name"] } </td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;">{$p["product_quantity"]}</td>
            </tr>
        {/foreach }
    </tbody>
</table>
<br/>
<br/>

<table style="width: 100%;">
	<tr>
		<td>
			<b>IMPORTANTE:</b> Ud. debe revisar junto con el distribuidor que las cantidades declaradas en la factura coincidan con la mercancía recibida.
            En caso de faltante, Ud. puede rechazar la entrega o anotar en las casillas siguientes lo productos. Estos productos serán repuestos a la mayor brevedad posible o, en caso de que el proveedor no tener existencias, el importe será devuelto al comprador.
            Una vez firmada y aceptada la factura, no se aceptan reclamaciones por faltante de producto.
		</td>
	</tr>
</table>
<br/>
<br/>
<table style="font-size:8pt;margin: 5px auto; width: 100%;">
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
<br/>
<br/>

<table style="width: 100%; margin: 30px auto;">
	<tr>
		<td> <strong>IMPORTANTE:</strong> En caso de que esta orden sea el resultado de una acción fraudulenta de la persona que realizó la compra, la firma de este comprobante significa que Ud. está de acuerdo en que, tanto la Tienda como los Transportistas, tienen el derecho de retirar los bienes relacionados en este comprobante.<br/>
            Será potestad única de la Tienda la determinación del carácter fraudulento de una orden y, a tal efecto, emitirá un documento que el transportista exhibirá en el momento de retirar los bienes. La Tienda se reserva el derecho de iniciar las acciones legales pertinentes contra la persona que efectuó la compra.
       </td>
	</tr>
</table>
<br/>
<br/>
<table style="font-size:8pt;margin: 5px auto; width: 100%;">
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

