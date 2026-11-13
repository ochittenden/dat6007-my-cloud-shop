<?php require_once __DIR__.'/includes/layout.php';$u=require_login();$orders=rows('SELECT id,total,status,created_at,payment_status FROM orders WHERE user_id=? ORDER BY created_at DESC',[$u['id']]);page_header('My orders');if($orders):?>
<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Placed (UTC)</th>
                <th>Total</th>
                <th>Status</th>
                <th>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $o):?>
                <tr>
                    <td>#<?=(int)$o['id']?>
                    </td>
                    <td><?=e($o['created_at'])?>
                    </td>
                    <td><?=money($o['total'])?>
                    </td>
                    <td>
                        <span class="status"><?=e(ucfirst($o['status']))?>
                        </span>
                    </td>
                    <td>
                        <a href="order.php?id=<?=$o['id']?>">Track order</a>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>
<?php else:?>
    <div class="panel">You haven't placed an order yet.</div>
<?php endif;page_footer(); ?>
