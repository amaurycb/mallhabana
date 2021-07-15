<style>
table, th, td {
  border-collapse: collapse;
  border-spacing: 0px;
}

th,td{
   border: 1px solid #000000;   
}
th{
  font-weigth:bold;
  font-size: 8px;
}

td {
  font-size: 7px;
}


</style>
 <h3>Fecha: <i>{$genDate}</i></h3>
 <h3>Generado el: <i>{date('Y-m-d')}</i></h3>
 <h3>Proveedor: <i>{$provider}</i></h3>
 <h3>Órdenes: <i>{$orders}</i></h3>
  <table width="100%" cellpadding="1" cellspacing="0" style="border-collapse: collapse;border-spacing: 0px;">
    
    {if $provider eq "C.C PALCO" or $provider eq "RANCHO PALCO"}

                <thead>
                    <tr>
                        <th width="9%"  style="font-weigth:bold;">C&oacute;digo</th>
                        <th width="15%" style="font-weigth:bold;">Nombre</th>
                        <th width="8%"   style="font-weigth:bold;">Cantidad</th>
                        <th width="55%" style="font-weigth:bold; text-align:center;">Orders</th>
                        <th width="7%" style="font-weigth:bold; text-align:center;">Precio Proveedor</th>
                        <th width="7%" style="font-weigth:bold; text-align:center;">Precio Total</th>
                    </tr>
                </thead>
                <tbody>
                {assign var="ptotal" value=0}
                    {foreach $products as $product}           
                        <tr style="font-size: 7pt;">
                            <td style=""  width="9%" >{$product["product_reference"] } </td>
                            <td style=""  width="15%" >{$product["product_name"] } </td>
                            <td style="text-align: center;"  width="8%" >{$product["qty"]}</td>
                            <td style="text-align: left;"  width="55%" >{$product["orders"]} </td>
                            <td style="text-align: center;"  width="7%" >{$product["original_wholesale_price"]|string_format:"%.2f"}</td>
                            <td style="text-align: center;"  width="7%" >{($product["qty"]*$product["original_wholesale_price"])|string_format:"%.2f"}</td>
                        </tr>
                        <br>
                     {$ptotal = $ptotal + $product["qty"]*$product["original_wholesale_price"]}   
                    {/foreach }
                <tr style="font-size: 7pt;">
                
                <td style=""  width="29%" >Subtotal: {$ptotal|string_format:"%.2f" } </td>
                 </tr>
                </tbody>

    {else}

            <thead>
                <tr>
                    <th width="9%"  style="font-weigth:bold;">C&oacute;digo</th>
                    <th width="15%" style="font-weigth:bold;">Nombre</th>
                    <th width="8%"   style="font-weigth:bold;">Cantidad</th>
                    <th width="61%" style="font-weigth:bold; text-align:center;">Orders</th>
                    <th width="7%" style="font-weigth:bold; text-align:center;">Stock</th>
                </tr>
            </thead>
            <tbody>
                {foreach $products as $product}           
                    <tr style="font-size: 7pt;">
                        <td style=""  width="9%" >{$product["product_reference"] } </td>
                        <td style=""  width="15%" >{$product["product_name"] } </td>
                        <td style="text-align: center;"  width="8%" >{$product["qty"]}</td>
                        <td style="text-align: left;"  width="61%" >{$product["orders"]} </td>
                        <td style="text-align: center;"  width="7%" >{$product["available"]}</td>
                    </tr>
                    <br>
                    
                {/foreach }
            </tbody>


    {/if}
</table>
