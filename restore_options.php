<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// Restore product_option_groups
DB::table('product_option_groups')->insert([
    ['id'=>1,'product_id'=>1,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>2,'product_id'=>1,'name'=>'Finish','slug'=>'finish','display_type'=>'cards','is_required'=>1,'sort_order'=>2,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>3,'product_id'=>2,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>4,'product_id'=>2,'name'=>'Finish','slug'=>'finish','display_type'=>'cards','is_required'=>1,'sort_order'=>2,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>5,'product_id'=>3,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>6,'product_id'=>3,'name'=>'Frame','slug'=>'frame','display_type'=>'cards','is_required'=>0,'sort_order'=>3,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>7,'product_id'=>4,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>8,'product_id'=>4,'name'=>'Frame','slug'=>'frame','display_type'=>'cards','is_required'=>0,'sort_order'=>3,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>9,'product_id'=>5,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>10,'product_id'=>5,'name'=>'Finish','slug'=>'finish','display_type'=>'cards','is_required'=>1,'sort_order'=>2,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>11,'product_id'=>6,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>12,'product_id'=>6,'name'=>'Frame','slug'=>'frame','display_type'=>'cards','is_required'=>1,'sort_order'=>3,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>13,'product_id'=>7,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>14,'product_id'=>8,'name'=>'Size','slug'=>'size','display_type'=>'buttons','is_required'=>1,'sort_order'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>15,'product_id'=>8,'name'=>'Finish','slug'=>'finish','display_type'=>'cards','is_required'=>1,'sort_order'=>2,'created_at'=>now(),'updated_at'=>now()],
]);

// Restore product_option_values
DB::table('product_option_values')->insert([
    ['id'=>1,'option_group_id'=>1,'label'=>'8×10 inch','value'=>'810-inch','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>2,'option_group_id'=>1,'label'=>'12×16 inch','value'=>'1216-inch','price_modifier'=>300,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>3,'option_group_id'=>1,'label'=>'16×20 inch','value'=>'1620-inch','price_modifier'=>600,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>4,'option_group_id'=>1,'label'=>'20×30 inch','value'=>'2030-inch','price_modifier'=>1200,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>5,'option_group_id'=>1,'label'=>'24×36 inch','value'=>'2436-inch','price_modifier'=>1800,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>6,'option_group_id'=>2,'label'=>'Glossy','value'=>'glossy','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>'High shine, vibrant colors','is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>7,'option_group_id'=>2,'label'=>'Matte','value'=>'matte','price_modifier'=>100,'price_type'=>'fixed','image'=>null,'description'=>'No glare, subtle elegance','is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>8,'option_group_id'=>2,'label'=>'Frosted','value'=>'frosted','price_modifier'=>200,'price_type'=>'fixed','image'=>null,'description'=>'Elegant frosted look','is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>9,'option_group_id'=>3,'label'=>'8×10 inch','value'=>'810-inch','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>10,'option_group_id'=>3,'label'=>'12×16 inch','value'=>'1216-inch','price_modifier'=>300,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>11,'option_group_id'=>3,'label'=>'16×20 inch','value'=>'1620-inch','price_modifier'=>600,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>12,'option_group_id'=>3,'label'=>'20×30 inch','value'=>'2030-inch','price_modifier'=>1200,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>13,'option_group_id'=>3,'label'=>'24×36 inch','value'=>'2436-inch','price_modifier'=>1800,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>14,'option_group_id'=>4,'label'=>'Glossy','value'=>'glossy','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>'High shine, vibrant colors','is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>15,'option_group_id'=>4,'label'=>'Matte','value'=>'matte','price_modifier'=>100,'price_type'=>'fixed','image'=>null,'description'=>'No glare, subtle elegance','is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>16,'option_group_id'=>4,'label'=>'Frosted','value'=>'frosted','price_modifier'=>200,'price_type'=>'fixed','image'=>null,'description'=>'Elegant frosted look','is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>17,'option_group_id'=>5,'label'=>'8×10 inch','value'=>'810-inch','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>18,'option_group_id'=>5,'label'=>'11×14 inch','value'=>'1114-inch','price_modifier'=>200,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>19,'option_group_id'=>5,'label'=>'16×20 inch','value'=>'1620-inch','price_modifier'=>500,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>20,'option_group_id'=>5,'label'=>'24×30 inch','value'=>'2430-inch','price_modifier'=>900,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>21,'option_group_id'=>5,'label'=>'30×40 inch','value'=>'3040-inch','price_modifier'=>1500,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>22,'option_group_id'=>6,'label'=>'Classic Black','value'=>'classic-black','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>'Elegant matte black wood frame','is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>23,'option_group_id'=>6,'label'=>'Natural Oak','value'=>'natural-oak','price_modifier'=>200,'price_type'=>'fixed','image'=>null,'description'=>'Warm natural oak finish','is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>24,'option_group_id'=>6,'label'=>'Walnut Brown','value'=>'walnut-brown','price_modifier'=>250,'price_type'=>'fixed','image'=>null,'description'=>'Rich walnut wood frame','is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>25,'option_group_id'=>6,'label'=>'White Minimal','value'=>'white-minimal','price_modifier'=>150,'price_type'=>'fixed','image'=>null,'description'=>'Clean white contemporary frame','is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>26,'option_group_id'=>6,'label'=>'Gold Accent','value'=>'gold-accent','price_modifier'=>400,'price_type'=>'fixed','image'=>null,'description'=>'Premium gold-finished frame','is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>27,'option_group_id'=>7,'label'=>'8×10 inch','value'=>'810-inch','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>28,'option_group_id'=>7,'label'=>'11×14 inch','value'=>'1114-inch','price_modifier'=>200,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>29,'option_group_id'=>7,'label'=>'16×20 inch','value'=>'1620-inch','price_modifier'=>500,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>30,'option_group_id'=>7,'label'=>'24×30 inch','value'=>'2430-inch','price_modifier'=>900,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>31,'option_group_id'=>7,'label'=>'30×40 inch','value'=>'3040-inch','price_modifier'=>1500,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>32,'option_group_id'=>8,'label'=>'Classic Black','value'=>'classic-black','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>'Elegant matte black wood frame','is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>33,'option_group_id'=>8,'label'=>'Natural Oak','value'=>'natural-oak','price_modifier'=>200,'price_type'=>'fixed','image'=>null,'description'=>'Warm natural oak finish','is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>34,'option_group_id'=>8,'label'=>'Walnut Brown','value'=>'walnut-brown','price_modifier'=>250,'price_type'=>'fixed','image'=>null,'description'=>'Rich walnut wood frame','is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>35,'option_group_id'=>8,'label'=>'White Minimal','value'=>'white-minimal','price_modifier'=>150,'price_type'=>'fixed','image'=>null,'description'=>'Clean white contemporary frame','is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>36,'option_group_id'=>8,'label'=>'Gold Accent','value'=>'gold-accent','price_modifier'=>400,'price_type'=>'fixed','image'=>null,'description'=>'Premium gold-finished frame','is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>37,'option_group_id'=>9,'label'=>'A4','value'=>'a4','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>38,'option_group_id'=>9,'label'=>'A3','value'=>'a3','price_modifier'=>100,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>39,'option_group_id'=>9,'label'=>'A2','value'=>'a2','price_modifier'=>250,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>40,'option_group_id'=>9,'label'=>'A1','value'=>'a1','price_modifier'=>500,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>41,'option_group_id'=>10,'label'=>'Matte','value'=>'matte','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>'Classic matte finish','is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>42,'option_group_id'=>10,'label'=>'Glossy','value'=>'glossy','price_modifier'=>50,'price_type'=>'fixed','image'=>null,'description'=>'High-shine glossy','is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>43,'option_group_id'=>10,'label'=>'Satin','value'=>'satin','price_modifier'=>75,'price_type'=>'fixed','image'=>null,'description'=>'Smooth satin texture','is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>44,'option_group_id'=>11,'label'=>'Small','value'=>'small','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>45,'option_group_id'=>11,'label'=>'Medium','value'=>'medium','price_modifier'=>400,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>46,'option_group_id'=>11,'label'=>'Large','value'=>'large','price_modifier'=>800,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>47,'option_group_id'=>11,'label'=>'Extra Large','value'=>'extra-large','price_modifier'=>1500,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>48,'option_group_id'=>12,'label'=>'Classic Black','value'=>'classic-black','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>'Elegant matte black wood frame','is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>49,'option_group_id'=>12,'label'=>'Natural Oak','value'=>'natural-oak','price_modifier'=>200,'price_type'=>'fixed','image'=>null,'description'=>'Warm natural oak finish','is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>50,'option_group_id'=>12,'label'=>'Walnut Brown','value'=>'walnut-brown','price_modifier'=>250,'price_type'=>'fixed','image'=>null,'description'=>'Rich walnut wood frame','is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>51,'option_group_id'=>12,'label'=>'White Minimal','value'=>'white-minimal','price_modifier'=>150,'price_type'=>'fixed','image'=>null,'description'=>'Clean white contemporary frame','is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>52,'option_group_id'=>12,'label'=>'Gold Accent','value'=>'gold-accent','price_modifier'=>400,'price_type'=>'fixed','image'=>null,'description'=>'Premium gold-finished frame','is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>53,'option_group_id'=>13,'label'=>'Small','value'=>'small','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>54,'option_group_id'=>13,'label'=>'Medium','value'=>'medium','price_modifier'=>400,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>55,'option_group_id'=>13,'label'=>'Large','value'=>'large','price_modifier'=>800,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>56,'option_group_id'=>13,'label'=>'Extra Large','value'=>'extra-large','price_modifier'=>1500,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>57,'option_group_id'=>14,'label'=>'8×10 inch','value'=>'810-inch','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>58,'option_group_id'=>14,'label'=>'12×16 inch','value'=>'1216-inch','price_modifier'=>300,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>59,'option_group_id'=>14,'label'=>'16×20 inch','value'=>'1620-inch','price_modifier'=>600,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>60,'option_group_id'=>14,'label'=>'20×30 inch','value'=>'2030-inch','price_modifier'=>1200,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>61,'option_group_id'=>14,'label'=>'24×36 inch','value'=>'2436-inch','price_modifier'=>1800,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>5,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>62,'option_group_id'=>15,'label'=>'Standard','value'=>'standard','price_modifier'=>0,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
    ['id'=>63,'option_group_id'=>15,'label'=>'Premium','value'=>'premium','price_modifier'=>300,'price_type'=>'fixed','image'=>null,'description'=>null,'is_default'=>0,'sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
]);

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Done!\n";
echo "Option Groups: " . DB::table('product_option_groups')->count() . "\n";
echo "Option Values: " . DB::table('product_option_values')->count() . "\n";
