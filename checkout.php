<?php require_once __DIR__.'/includes/layout.php';$u=require_login();
if(empty($_SESSION['cart'])){flash('error','Add products before checking out.');redirect('cart.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_post();$name=request_string('shipping_name',120);$address=request_string('shipping_address',400);$method=request_string('payment_method',20);$testCode=request_string('test_code',20);
 if(mb_strlen($name)<2 || mb_strlen($address)<8 || !in_array($method,['demo_card','demo_wallet','demo_bank'],true) || !in_array($testCode,['SUCCESS','DECLINE'],true)) {flash('error','Please complete every checkout field correctly.');redirect('checkout.php');}
 if($testCode==='DECLINE'){flash('error','Simulated payment declined. No order was placed.');redirect('checkout.php');}
 $conn=db();if(!sqlsrv_begin_transaction($conn))throw new RuntimeException('Could not begin checkout.');
 try{
  $items=[];$total=0;
  foreach($_SESSION['cart'] as $id=>$qty){
   $id=(int)$id;$qty=(int)$qty;
   if($id<1||$qty<1||$qty>100)throw new RuntimeException('Invalid cart quantity.');
   $product=one('SELECT id,name,price FROM products WHERE id=? AND active=1',[$id]);
   if(!$product)throw new RuntimeException('A cart product is no longer available.');
   // An atomic conditional update prevents concurrent orders overselling stock.
   $updated=query('UPDATE products SET stock=stock-?,updated_at=SYSUTCDATETIME() WHERE id=? AND active=1 AND stock>=?',[$qty,$id,$qty]);
   if(sqlsrv_rows_affected($updated)!==1)throw new RuntimeException('Insufficient stock for '.$product['name'].'.');
   sqlsrv_free_stmt($updated);$items[]=['product'=>$product,'quantity'=>$qty];$total+=(float)$product['price']*$qty;
  }
  // Demo-only reference: never store or process real payment instruments.
  $reference='DEMO-'.strtoupper(bin2hex(random_bytes(10)));
  $orderStmt=query('INSERT INTO orders(user_id,total,payment_method,payment_reference,shipping_name,shipping_address) OUTPUT INSERTED.id VALUES(?,?,?,?,?,?)',[$u['id'],number_format($total,2,'.',''),$method,$reference,$name,$address]);
  $orderRow=sqlsrv_fetch_array($orderStmt,SQLSRV_FETCH_ASSOC);sqlsrv_free_stmt($orderStmt);$orderId=(int)$orderRow['id'];
  foreach($items as $it){$p=$it['product'];query('INSERT INTO order_items(order_id,product_id,product_name,quantity,unit_price) VALUES(?,?,?,?,?)',[$orderId,$p['id'],$p['name'],$it['quantity'],$p['price']]);}
  query('INSERT INTO order_history(order_id,status,note) VALUES(?,?,?)',[$orderId,'placed','Order submitted and demo payment approved']);
  if(!sqlsrv_commit($conn))throw new RuntimeException('Could not commit order.');
  unset($_SESSION['cart']);log_event('order_placed',(int)$u['id']);flash('success','Virtual payment approved. Your order has been placed.');redirect('order.php?id='.$orderId);
 }catch(Throwable $e){sqlsrv_rollback($conn);error_log('Checkout error: '.$e->getMessage());flash('error','Checkout failed: '.(str_starts_with($e->getMessage(),'Insufficient stock')?$e->getMessage():'Please check stock and try again.'));redirect('cart.php');}
}
$total=0;$items=[];foreach($_SESSION['cart'] as $id=>$qty){$p=one('SELECT name,price,stock,active FROM products WHERE id=?',[(int)$id]);if(!$p||!$p['active']||(int)$p['stock']<(int)$qty){flash('error','Your cart contains a sold-out or unavailable item.');redirect('cart.php');}$items[]=['name'=>$p['name'],'qty'=>$qty,'price'=>$p['price']];$total+=(float)$p['price']*(int)$qty;}
page_header('Secure demo checkout');?>
<div class="two-cols">
    <div class="panel">
        <p class="muted">This is a simulated transaction. Do not enter real bank or card details.</p>
        <form method="post"><?=csrf_field()?>
            <label>Recipient name</label>
            <input name="shipping_name" value="<?=e($u['name'])?>" maxlength="120" required>
            <label>Delivery address</label>
            <textarea name="shipping_address" maxlength="400" minlength="8" required placeholder="Street, city, postcode">
        </textarea>
        <label>Virtual payment method</label>
        <select name="payment_method" required>
            <option value="demo_card">Demo card</option>
            <option value="demo_wallet">Demo wallet</option>
            <option value="demo_bank">Demo bank transfer</option>
        </select>
        <label>Transaction simulation</label>
        <select name="test_code">
            <option value="SUCCESS">SUCCESS — approve test payment</option>
            <option value="DECLINE">DECLINE — reject test payment</option>
        </select>
        <div class="form-actions">
            <button class="btn">Place demo order</button>
        </div>
    </form>
</div>
<div class="panel">
    <h2>Order summary</h2>
    <?php foreach($items as $i):?>
        <p class="flex-between">
            <span><?=e($i['name'])?> × <?=(int)$i['qty']?>
            </span>
            <b><?=money((float)$i['price']*$i['qty'])?>
        </b>
    </p>
<?php endforeach;?>
<hr>
<div class="flex-between">
    <strong>Total</strong>
    <strong class="price"><?=money($total)?>
    </strong>
</div>
</div>
</div>
<?php page_footer(); ?>
