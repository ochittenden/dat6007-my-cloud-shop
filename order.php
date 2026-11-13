<?php require_once __DIR__.'/includes/layout.php';$u=require_login();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$o=$id?one('SELECT * FROM orders WHERE id=? AND (user_id=? OR ?=1)',[$id,$u['id'],$u['role']==='admin'?1:0]):null;
if(!$o){http_response_code(404);page_header('Order not found');echo '<div class="panel">Order unavailable.</div>';page_footer();exit;}
$items=rows('SELECT product_name,quantity,unit_price FROM order_items WHERE order_id=?',[$id]);$history=rows('SELECT status,note,created_at FROM order_history WHERE order_id=? ORDER BY id',[$id]);page_header('Order #'.$id);?>
<div class="two-cols">
    <div class="panel">
        <h2>Tracking timeline</h2>
        <p>Current status: <span class="status"><?=e(ucfirst($o['status']))?>
        </span>
    </p>
    <ol class="steps">
    <?php foreach($history as $h):?>
        <li>
            <strong><?=e(ucfirst($h['status']))?>
            </strong> <span class="muted small"><?=e($h['created_at'])?> UTC</span>
            <div><?=e($h['note'])?>
            </div>
        </li>
    <?php endforeach;?>
</ol>
<p class="muted small">Tracking updates appear when an administrator changes the status. Refresh this page for the latest status.</p>
<h3>Delivery details</h3>
<p><?=e($o['shipping_name'])?>
    <br><?=nl2br(e($o['shipping_address']))?>
</p>
</div>
<div class="panel">
    <h2>Order details</h2>
    <?php foreach($items as $it):?>
        <p class="flex-between">
            <span><?=e($it['product_name'])?> × <?=(int)$it['quantity']?>
            </span>
            <strong><?=money((float)$it['unit_price']*$it['quantity'])?>
            </strong>
        </p>
    <?php endforeach;?>
    <hr>
    <p class="flex-between">
        <strong>Total</strong>
        <strong><?=money($o['total'])?>
        </strong>
    </p>
    <p class="muted small">Payment: <?=e(str_replace('_',' ',$o['payment_method']))?> · <?=e($o['payment_status'])?>
    </p>
    <p class="muted small">Reference: <?=e($o['payment_reference'])?>
    </p>
</div>
</div>
<?php page_footer(); ?>
