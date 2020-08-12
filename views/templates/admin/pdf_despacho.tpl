 <h3>Fecha: <i>{$genDate}</i></h3>
 <h3>Generado el: <i>{date('Y-m-d')}</i></h3>
 <h3>Proveedor: <i>{$provider}</i></h3>
 <h3>Órdenes: <i>{$orders}</i></h3>
 <table>
    <thead>
        <tr>
            <th style="border:1px #666 solid;  padding: 2px; background: #ffffb0; font-size: 14px; font-weigth:bold;">C&oacute;digo</th>
            <th style="border:1px #666 solid; padding: 2px; background: #ffffb0; font-size: 14px; font-weigth:bold;">Nombre</th>
            <th style="border:1px #666 solid; padding: 2px; background: #ffffb0; font-size: 14px; font-weigth:bold; text-align:center;">Cantidad</th>
            <th style="border:1px #666 solid; padding: 2px; background: #ffffb0; font-size: 14px; font-weigth:bold; text-align:center;">Orders</th>
            <th style="border:1px #666 solid; padding: 2px; background: #ffffb0; font-size: 14px; font-weigth:bold; text-align:center;">Stock</th>
        </tr>
    </thead>
    <tbody>
        {foreach $products as $product}           
            <tr style="font-size: 10pt;">
                <td style="border:1px #666 solid; padding: 2px;">{$product["product_reference"] } </td>
                <td style="border:1px #666 solid; padding: 2px;">{$product["product_name"] } </td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;">{$product["qty"]}</td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;">{$product["orders"]}</td>
                <td style="border:1px #666 solid;  padding: 2px; text-align: center;">{$product["available"]}</td>
            </tr>
        {/foreach }
    </tbody>
</table>