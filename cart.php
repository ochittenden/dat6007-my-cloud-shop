<?php require_once __DIR__.'/includes/layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_post();$action=request_string('action',20);$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$qty=filter_var($_POST['quantity']??1,FILTER_VALIDATE_INT);
 if($action==='clear'){unset($_SESSION['cart']);redirect('cart.php');}
 if(!$id || $id<1){flash('error','Invalid product.');redirect('cart.php');}
 if($action==='remove'){unset($_SESSION['cart'][$id]);redirect('cart.php');}
 if(!in_array($action,['add','update'],true) || !$qty || $qty<1 || $qty>100){flash('error','Invalid quantity.');redirect('cart.php');}
 $p=one('SELECT stock FROM products WHERE id=? AND active=1',[$id]);
 if(!$p || (int)$p['stock']<1){flash('error','This item is unavailable.');redirect('cart.php');}
 $newQty=$action==='add'?((int)($_SESSION['cart'][$id]??0)+$qty):$qty;
 $_SESSION['cart'][$id]=min($newQty,(int)$p['stock'],100);flash('success','Your cart has been updated.');redirect('cart.php');
}
$cart=$_SESSION['cart']??[];$items=[];$total=0;
foreach($cart as $id=>$qty){$p=one('SELECT id,name,price,stock,active FROM products WHERE id=?',[(int)$id]);if(!$p || !(int)$p['active'])continue;$p['quantity']=min((int)$qty,(int)$p['stock']);if($p['quantity']<1)continue;$p['line_total']=(float)$p['price']*$p['quantity'];$items[]=$p;$total+=$p['line_total'];}
page_header('Shopping cart');if(!$items):?>
<div class="panel">
    <p>Your cart is empty.</p>
    <a class="btn" href="index.php">Browse products</a>
</div>
<?php else:?>
    <div class="two-cols">
        <div class="panel table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $p):?>
                        <tr>
                            <td>
                                <a href="product.php?id=<?=(int)$p['id']?>"><?=e($p['name'])?>
                            </a>
                        </td>
                        <td><?=money($p['price'])?>
                        </td>
                        <td>
                            <form method="post"><?=csrf_field()?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?=$p['id']?>">
                                <input type="number" name="quantity" min="1" max="<?=min(100,(int)$p['stock'])?>" value="<?=$p['quantity']?>" style="width:75px">
                                <button class="btn secondary">Update</button>
                            </form>
                        </td>
                        <td><?=money($p['line_total'])?>
                        </td>
                        <td>
                            <form method="post"><?=csrf_field()?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?=$p['id']?>">
                                <button class="link-button">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    <div class="panel">
        <h2>Order summary</h2>
        <div class="flex-between">
            <span>Total</span>
            <strong class="price"><?=money($total)?>
            </strong>
        </div>
        <p class="muted small">Virtual checkout. No payment is taken.</p>
        <a class="btn" href="checkout.php">Continue to checkout</a>
    </div>
</div>
<?php endif;page_footer(); ?>
