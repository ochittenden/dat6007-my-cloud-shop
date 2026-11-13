<?php require_once __DIR__.'/includes/layout.php';$u=require_admin();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$p=$id?one('SELECT * FROM products WHERE id=?',[$id]):null;if($id && !$p){http_response_code(404);exit('Product not found');}
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_post();$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$name=request_string('name',180);$description=request_string('description',5000);$category=request_string('category',80);$price=check_amount(request_string('price',20));$stock=filter_var($_POST['stock']??null,FILTER_VALIDATE_INT);$image=request_string('image_url',500);$active=isset($_POST['active'])?1:0;
 if(mb_strlen($name)<2 || $description==='' || $category==='' || !$price || $stock===false || $stock<0 || $stock>1000000 || ($image!=='' && (!filter_var($image,FILTER_VALIDATE_URL) || !str_starts_with($image,'https://')))){flash('error','Check the product details. Image URLs must be HTTPS.');redirect('admin_product.php'.($id?'?id='.$id:''));}
 if($id){query('UPDATE products SET name=?,description=?,category=?,price=?,stock=?,image_url=?,active=?,updated_at=SYSUTCDATETIME() WHERE id=?',[$name,$description,$category,$price,$stock,$image?:null,$active,$id]);}
 else query('INSERT INTO products(name,description,category,price,stock,image_url,active) VALUES(?,?,?,?,?,?,?)',[$name,$description,$category,$price,$stock,$image?:null,$active]);
 log_event('product_updated',(int)$u['id']);flash('success','Product saved.');redirect('admin.php');
}
page_header($p?'Edit product':'Add product');?>
<div class="panel">
    <form method="post"><?=csrf_field()?>
        <input type="hidden" name="id" value="<?=e($p['id']??'')?>">
        <label>Name</label>
        <input name="name" value="<?=e($p['name']??'')?>" maxlength="180" required>
        <label>Description</label>
        <textarea name="description" required><?=e($p['description']??'')?>
    </textarea>
    <label>Category</label>
    <input name="category" value="<?=e($p['category']??'')?>" required>
    <label>Price (£)</label>
    <input name="price" value="<?=e($p['price']??'')?>" inputmode="decimal" required>
    <label>Stock</label>
    <input name="stock" type="number" value="<?=e($p['stock']??0)?>" min="0" max="1000000" required>
    <label>Image URL (optional HTTPS)</label>
    <input name="image_url" value="<?=e($p['image_url']??'')?>">
    <label>
        <input type="checkbox" name="active" <?=!isset($p)||$p['active']?'checked':''?>> Published</label>
        <div class="form-actions">
            <button class="btn">Save product</button>
            <a class="btn secondary" href="admin.php">Cancel</a>
        </div>
    </form>
</div>
<?php page_footer(); ?>
