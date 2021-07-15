<?php
/** @var $data array */
/** @var $orders TbOrders[] */
/** @var $providerGroup string */

$idOrders = array();
foreach($orders as $o) $idOrders[] = $o->ID_ORDER;

// $this->renderPartial('//mails/_header', array('title' => 'Despacho'));
?>
<div style="padding: 10px;">

    <h3>Despacho de Productos, Proveedor: <?php echo $providerGroup ?></h3>
    <p>Fecha: <?php echo date('Y-m-d')?></p>
    <p style="margin-bottom: 30px;">Ordenes: <?php echo implode(', ',$idOrders)?></p>
    <table width="100%" class="filled ver">
        <tr>
            <th>C&oacute;digo</th>
            <th>Nombre</th>
            <th style="text-align: center;">Cantidad</th>
            <th>Orders</th>
            <th style="text-align: center;">Stock</th>
        </tr>
        <?php foreach($data as $item) { ?>
        <tr>
            <td colspan="5" class="title"><?php echo $item['NAME'] ?></td>
        </tr>
            <?php foreach($item['PRODUCTS'] as $idProd => $prod) { ?>
            <tr style="font-size: 10pt;">
                <td><?php echo $prod['CODE']?></td>
                <td><?php echo $prod['NAME']?></td>
                <td style="text-align: center;"><?php echo $prod['CANT']?></td>
                <td style="font-size: 8pt;">
                <?php
                    $arr = array();
                    foreach($prod['ORDERS'] as $idOrder => $q) $arr[] = $idOrder . ' (' . $q . ')';
                    echo implode(', ', $arr);
                ?>
                </td>
                <td style="text-align: center;"><?php echo $prod['STOCK']?></td>
            </tr>
            <?php } ?>
        <?php } ?>
   </table>

</div>
<?php
// $this->renderPartial('//mails/_footer');
?>