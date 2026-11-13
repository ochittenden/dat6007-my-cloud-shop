<?php require_once __DIR__.'/includes/bootstrap.php';check_post();$u=require_admin();$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$status=request_string('status',30);$allowed=['placed','processing','shipped','delivered','cancelled'];
if(!$id || !in_array($status,$allowed,true)) {flash('error','Invalid order update.');redirect('admin.php');}
$conn=db();sqlsrv_begin_transaction($conn);
try{
 $order=one('SELECT status FROM orders WITH (UPDLOCK,ROWLOCK) WHERE id=?',[$id]);if(!$order)throw new RuntimeException('Order missing');
 // Cancelled orders are terminal; stock is returned on cancellation once only.
 if($order['status']==='cancelled' && $status!=='cancelled')throw new RuntimeException('Cancelled orders cannot be reopened.');
 if($order['status']==='delivered' && $status!=='delivered')throw new RuntimeException('Delivered orders are final.');
 if($status==='cancelled' && $order['status']!=='cancelled'){
  foreach(rows('SELECT product_id,quantity FROM order_items WHERE order_id=?',[$id]) as $item) query('UPDATE products SET stock=stock+?,updated_at=SYSUTCDATETIME() WHERE id=?',[(int)$item['quantity'],(int)$item['product_id']]);
 }
 if($order['status']!==$status){query('UPDATE orders SET status=? WHERE id=?',[$status,$id]);query('INSERT INTO order_history(order_id,status,note) VALUES(?,?,?)',[$id,$status,'Status updated by store administration']);}
 if(!sqlsrv_commit($conn))throw new RuntimeException('Commit failed');flash('success','Order updated.');
}catch(Throwable $e){sqlsrv_rollback($conn);error_log('Order update failed: '.$e->getMessage());flash('error',$e->getMessage());}
redirect('admin.php');
