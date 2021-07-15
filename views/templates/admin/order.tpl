<div class="panel kpi-container">
    <div class="row">
      <div class="col-xs-6 col-sm-3 box-stats color3">
        <div class="kpi-content">
          <i class="icon-calendar-empty"></i>
          <span class="title">Fecha y Hora</span>
          <span class="value">{$order->date_add}</span>
        </div>
      </div>
      <div class="col-xs-6 col-sm-3 box-stats color4">
        <div class="kpi-content">
          <i class="icon-money"></i>
          <span class="title">Total</span>
          <span class="value">{displayPrice price=$payment->amount currency=$currency->id}</span>
        </div>
      </div>      
      <div class="col-xs-6 col-sm-3 box-stats color1">
        <a href="#start_products">
          <div class="kpi-content">
            <i class="icon-book"></i>
            <span class="title">Productos</span>
            <span class="value">{count($order->getProducts())}</span>
          </div>
        </a>
      </div>
    </div>
  </div>

<div class="row">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-credit-card"></i>
                Pedido
                <span class="badge">{$order->reference}</span>
                <span class="badge">N°{$order->id}</span>
            </div>
            <div class="table-responsive">
              <table class="table history-status row-margin-bottom">
                <tbody>
                  {foreach from=$history item=row key=key}
                    {if ($key == 0)}
                      <tr>
                        <td style="background-color:{$row['color']}"><img src="../img/os/{$row['id_order_state']|intval}.gif" width="16" height="16" alt="{$row['ostate_name']|stripslashes}" /></td>
                        <td style="background-color:{$row['color']};color:{$row['text-color']}">{$row['ostate_name']|stripslashes}</td>
                        <td style="background-color:{$row['color']};color:{$row['text-color']}">{if $row['employee_lastname']}{$row['employee_firstname']|stripslashes} {$row['employee_lastname']|stripslashes}{/if}</td>
                        <td style="background-color:{$row['color']};color:{$row['text-color']}">{dateFormat date=$row['date_add'] full=true}</td>
                      </tr>
                    {else}
                      <tr>
                        <td><img src="../img/os/{$row['id_order_state']|intval}.gif" width="16" height="16" /></td>
                        <td>{$row['ostate_name']|stripslashes}</td>
                        <td>{if $row['employee_lastname']}{$row['employee_firstname']|stripslashes} {$row['employee_lastname']|stripslashes}{else}&nbsp;{/if}</td>
                        <td>{dateFormat date=$row['date_add'] full=true}</td>
                       </tr>
                    {/if}
                  {/foreach}
                </tbody>
              </table>
            </div>
            <hr>
            <ul class="nav nav-tabs" id="myTab">          
                <li class="active">
                    <a href="#shipping">
                    <i class="icon-truck "></i>
                    Transporte
                    </a>
                </li>
            </ul>
            <div class="tab-content panel">
                <div class="tab-pane active" id="shipping">
                    {if !empty($carrier->name)}
                        <b>Transportista:</b> {$carrier->name}<br/>
                        <b>Tiempo de entrega:</b> {$carrier->delay}<br/>
                        {if $is_admin}
                        <b>Costo:</b> {number_format($order->total_shipping_tax_incl)}<br/>
                        {/if}
                    {else}
                        <b>Orden sin transportación</b>
                    {/if}
                    <!-- Shipping block -->
                </div>
            </div>
            
            <hr>
            <ul class="nav nav-tabs" id="myTab">          
                <li class="active">
                    <a href="#shipping">
                    <i class="icon-money "></i>
                        Pago
                    </a>
                </li>
            </ul>
            <div class="tab-content panel">
                <div class="tab-pane active" id="shipping">
                    <div class="table-responsive">
                        <table class="table history-status row-margin-bottom">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Método de pago</th>
                                    <th>Id Transacción</th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{$payment->date_add}</td>
                                    <td>{$payment->payment_method}</td>
                                    <td>{$payment->transaction_id}</td>
                                    <td> {displayPrice price=$payment->amount currency=$currency->id}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-user"></i>
                Cliente
                <span class="badge">{$customer->firstname} {$customer->lastname}</span>
                <span class="badge">N°{$customer->id}</span>
            </div>
            <dl class="well list-detail">
                <dt>Nombre</dt>
                    <dd>{$customer->firstname}</dd>
                <dt>Apellidos</dt>
                    <dd>{$customer->lastname}</dd>
                  <dt>Dirección de correo electrónico</dt>
                    <dd><a href="mailto:{$customer->email}"><i class="icon-envelope-o"></i> {$customer->email}</a></dd>
                  <dt>Cuenta registrada</dt>
                    <dd class="text-muted"><i class="icon-calendar-o"></i> {$customer->date_add}</dd>
            </dl>
             <dl class="well list-detail">
                <dt>Dirección de Entrega</dt>
                    <dd>{$address->address1}, Entre: {$address->address2}, {$address->city}, {$state->name}</dd>
                <dt>Provincia</dt>
                    <dd>{$country->name}</dd>
            </dl>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <div class="table-responsive">
                <table class="table history-status row-margin-bottom">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $products as $product}
                            {if $is_admin || $product['owner']}
                                <tr class="product-line-row">
                                    <td>{if isset($product.image) && $product.image->id}{$product.image_tag}{/if}</td>
                                    <td>
                                            <span class="productName">{$product['product_name']}</span><br />
                                            {if $product.product_reference}{l s='Reference number:' d='Admin.Orderscustomers.Feature'} {$product.product_reference}<br />{/if}
                                            {if $product.product_supplier_reference}{l s='Supplier reference:' d='Admin.Orderscustomers.Feature'} {$product.product_supplier_reference}{/if}
                                        {if isset($product.pack_items) && $product.pack_items|@count > 0}<br>
                                            <button name="package" class="btn btn-default" type="button" onclick="TogglePackage('{$product['id_order_detail']}'); return false;" value="{$product['id_order_detail']}">{l s='Package content' d='Admin.Orderscustomers.Feature'}</button>
                                        {/if}
                                        <div class="row-editing-warning" style="display:none;">
                                            <div class="alert alert-warning">
                                                <strong>{l s='Editing this product line will remove the reduction and base price.' d='Admin.Orderscustomers.Notification'}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {$product['product_quantity']}
                                    </td>
                                    <td>
                                        <span class="product_price_show">{displayPrice price=$product['product_price'] currency=$currency->id}</span>
                                    
                                    </td>
                                    
                                    <td class="total_product">
                                        {displayPrice price=(Tools::ps_round($product['product_price'] , 2) * ($product['product_quantity'])) currency=$currency->id}
                                    </td>
                                    <td colspan="2" style="display: none;" class="add_product_fields">&nbsp;</td>
                                    <td class="cancelCheck standard_refund_fields current-edit" style="display:none">
                                        <input type="hidden" name="totalQtyReturn" id="totalQtyReturn" value="{$product['product_quantity_return']}" />
                                        <input type="hidden" name="totalQty" id="totalQty" value="{$product['product_quantity']}" />
                                        <input type="hidden" name="productName" id="productName" value="{$product['product_name']}" />
                                    {if ((!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN')) AND (int)($product['product_quantity_return']) < (int)($product['product_quantity']))}
                                        <input type="checkbox" name="id_order_detail[{$product['id_order_detail']}]" id="id_order_detail[{$product['id_order_detail']}]" value="{$product['id_order_detail']}" onchange="setCancelQuantity(this, {$product['id_order_detail']}, {$product['product_quantity'] - $product['customizationQuantityTotal'] - $product['product_quantity_return'] - $product['product_quantity_refunded']})" {if ($product['product_quantity_return'] + $product['product_quantity_refunded'] >= $product['product_quantity'])}disabled="disabled" {/if}/>
                                    {else}
                                        --
                                    {/if}
                                    </td>
                                    <td class="cancelQuantity standard_refund_fields current-edit" style="display:none">
                                    {if ($product['product_quantity_return'] + $product['product_quantity_refunded'] >= $product['product_quantity'])}
                                        <input type="hidden" name="cancelQuantity[{$product['id_order_detail']}]" value="0" />
                                    {elseif (!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN'))}
                                        <input type="text" id="cancelQuantity_{$product['id_order_detail']}" name="cancelQuantity[{$product['id_order_detail']}]" onchange="checkTotalRefundProductQuantity(this)" value="" />
                                    {/if}

                                    {if $product['customizationQuantityTotal']}
                                        {assign var=productQuantity value=($product['product_quantity']-$product['customizationQuantityTotal'])}
                                    {else}
                                        {assign var=productQuantity value=$product['product_quantity']}
                                    {/if}

                                    {if ($order->hasBeenDelivered())}
                                        {$product['product_quantity_refunded']}/{$productQuantity-$product['product_quantity_refunded']}
                                    {elseif ($order->hasBeenPaid())}
                                        {$product['product_quantity_return']}/{$productQuantity}
                                    {else}
                                        0/{$productQuantity}
                                    {/if}
                                    </td>
                                </tr>
                            {/if}
                        {/foreach}

                    </tbody>
                    {if $is_admin}
                    <tfoot>
                        <tr>
                            <th colspan="4" style="text-align:right;">Total Productos</th>
                            <th> {displayPrice price=$order->total_products currency=$currency->id}</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="text-align:right;">Total Transpotación</th>
                            <th> {displayPrice price=$order->total_shipping_tax_incl currency=$currency->id}</th>
                        </tr>
                         <tr>
                            <th colspan="4" style="text-align:right;">TOTAL PAGADO</th>
                            <th> {displayPrice price=$order->total_paid currency=$currency->id}</th>
                        </tr>
                    </tfoot>
                    {/if}
                </table>
            </div>
        </div>
    </div>
</div>
