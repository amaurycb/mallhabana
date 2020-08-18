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
        <td> </td>
        <td style="text-align:right; ">
            <h2>{$supplier}</h2>
            <h3 style="color: #333">Orden: <i>{$item->id}</i></h3>
            <h3 style="color: #333">Fecha: <i>{$item->date_add}</i></h3>
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
            <th style="background: #ffffb0; font-size: 13px; font-weigth:bold; text-align:center;">Precio</th>
            <th style="background: #ffffb0; font-size: 13px; font-weigth:bold; text-align:center;">Total</th>
        </tr>
    </thead>
    <tbody>
        {foreach $products[$item->id] as $p}           
            <tr style="font-size: 10pt;">
                <td style="border:1px #666 solid; padding: 2px;">{$p["product_reference"] } </td>
                <td style="border:1px #666 solid; padding: 2px;">{$p["product_name"] } </td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;">{$p["product_quantity"]}</td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;">{number_format($p["original_wholesale_price"],2)}</td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;">{number_format($p["original_wholesale_price"] * $p["product_quantity"],2)}</td>
            </tr>
        {/foreach }
		<tr>
			<td colspan="4"  style="border:1px #666 solid;  padding: 2px; text-align: left;">Total de la Orden</td>
			<td  style="border:1px #666 solid;  padding: 2px; text-align: center;">{number_format($customers[$item->id]['totalOrder'], 2)}</td>
		</tr>
    </tbody>
</table>
</tr>
</table>
{/foreach}

