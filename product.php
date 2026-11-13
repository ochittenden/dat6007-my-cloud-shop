<?php require_once __DIR__.'/includes/layout.php';$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$p=$id?one('SELECT * FROM products WHERE id=? AND active=1',[$id]):null;
if(!$p){http_response_code(404);page_header('Product not found');echo '<div class="panel">This product is unavailable.</div>';page_footer();exit;}
page_header($p['name']);?>
<div class="product-detail">
    <div>
        <?php if($p['image_url']):?>
            <img class="item-image" src="<?=e($p['image_url'])?>" alt="<?=e($p['name'])?>">
        <?php else:?>
            <div class="product-photo item-image">
                <span class="photo-placeholder">✦</span>
            </div>
        <?php endif;?>
    </div>
    <div class="panel">
        <small><?=e($p['category'])?>
    </small>
    <h2><?=e($p['name'])?>
    </h2>
    <p><?=e($p['description'])?>
    </p>
    <div class="price"><?=money($p['price'])?>
    </div>
    <p class="muted"><?=((int)$p['stock'])?> available</p>
    <form method="post" action="cart.php"><?=csrf_field()?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?=(int)$p['id']?>">
        <label>Quantity</label>
        <input type="number" min="1" max="<?=max(1,(int)$p['stock'])?>" name="quantity" value="1">
        <div class="form-actions">
            <button class="btn" <?=(int)$p['stock']<1?'disabled':''?>>Add to cart</button>
        </div>
    </form>
    <?php if(current_user()):?>
        <form method="post" action="wishlist.php" class="form-actions"><?=csrf_field()?>
            <input type="hidden" name="id" value="<?=(int)$p['id']?>">
            <input type="hidden" name="action" value="toggle">
            <button class="btn secondary">♡ Save / unsave wishlist</button>
        </form>
    <?php endif;?>
</div>
</div>
<?php page_footer();?>
