<?php require_once __DIR__.'/includes/layout.php';$u=require_admin();
$stats=one('SELECT (SELECT COUNT(*) FROM products) AS products,(SELECT COUNT(*) FROM orders) AS orders,(SELECT COUNT(*) FROM shopusers) AS users');
$products=rows('SELECT id,name,category,price,stock,active FROM products ORDER BY id DESC');
$orders=rows('SELECT TOP 75 o.id,o.status,o.total,o.created_at,u.email FROM orders o JOIN shopusers u ON o.user_id=u.id ORDER BY o.id DESC');
page_header('Store administration');?>
<div class="stat-grid">
    <div class="stat">
        <span class="muted">Products</span>
        <br>
        <b><?=(int)$stats['products']?>
    </b>
</div>
<div class="stat">
    <span class="muted">Orders</span>
    <br>
    <b><?=(int)$stats['orders']?>
</b>
</div>
<div class="stat">
    <span class="muted">Customers</span>
    <br>
    <b><?=(int)$stats['users']?>
</b>
</div>
</div>
<div class="panel">
    <div class="flex-between">
        <h2>Catalog management</h2>
        <a class="btn" href="admin_product.php">Add product</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Visibility</th>
                    <th>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $p):?>
                    <tr>
                        <td><?=e($p['name'])?>
                        </td>
                        <td><?=e($p['category'])?>
                        </td>
                        <td><?=money($p['price'])?>
                        </td>
                        <td><?=(int)$p['stock']?>
                        </td>
                        <td><?=$p['active']?'Published':'Hidden'?>
                        </td>
                        <td>
                            <a href="admin_product.php?id=<?=$p['id']?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</div>
<div class="panel">
    <h2>Recent orders</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o):?>
                    <tr>
                        <td>
                            <a href="order.php?id=<?=$o['id']?>">#<?=$o['id']?>
                        </a>
                    </td>
                    <td><?=e($o['email'])?>
                    </td>
                    <td><?=money($o['total'])?>
                    </td>
                    <td>
                        <span class="status"><?=e($o['status'])?>
                        </span>
                    </td>
                    <td>
                        <form action="admin_order.php" method="post"><?=csrf_field()?>
                            <input type="hidden" name="id" value="<?=$o['id']?>">
                            <select name="status" aria-label="Order status">
                                <option value="placed" <?=$o['status']==='placed'?'selected':''?>>Placed</option>
                                <option value="processing" <?=$o['status']==='processing'?'selected':''?>>Processing</option>
                                <option value="shipped" <?=$o['status']==='shipped'?'selected':''?>>Shipped</option>
                                <option value="delivered" <?=$o['status']==='delivered'?'selected':''?>>Delivered</option>
                                <option value="cancelled" <?=$o['status']==='cancelled'?'selected':''?>>Cancelled</option>
                            </select>
                            <button class="btn secondary">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>
</div>
<?php page_footer(); ?>
