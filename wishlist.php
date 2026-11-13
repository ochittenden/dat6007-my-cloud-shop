<?php require_once __DIR__.'/includes/layout.php';$user=require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_post();$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
 if($id && one('SELECT id FROM products WHERE id=? AND active=1',[$id])){
  if(one('SELECT product_id FROM wishlist WHERE user_id=? AND product_id=?',[$user['id'],$id]))query('DELETE FROM wishlist WHERE user_id=? AND product_id=?',[$user['id'],$id]);
  else query('INSERT INTO wishlist(user_id,product_id) VALUES(?,?)',[$user['id'],$id]);
 }redirect('wishlist.php');
}
$products=rows('SELECT p.id,p.name,p.description,p.category,p.price,p.stock,p.image_url FROM wishlist w JOIN products p ON w.product_id=p.id WHERE w.user_id=? AND p.active=1 ORDER BY w.created_at DESC',[$user['id']]);
page_header('Your wishlist');if($products):?>
<div class="grid">
    <?php foreach($products as $p)product_card($p);?>
</div>
<?php else:?>
    <div class="panel">Nothing saved yet. Browse products to add favourites.</div>
<?php endif;page_footer(); ?>
