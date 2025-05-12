<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MigrateOldDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Step 1: Migrate Universities
        $this->migrateUniversities();
        
        // Step 2: Migrate Users
        $this->migrateUsers();
        
        // Step 3: Migrate Stores
        $this->migrateStores();
        
        // Step 4: Migrate Categories
        $this->migrateCategories();
        
        // Step 5: Migrate Products
        $this->migrateProducts();
        
        // Step 6: Migrate Product Requests
        $this->migrateProductRequests();
    }
    
    /**
     * Migrate universities from uni_data to universities table
     */
    private function migrateUniversities(): void
    {
        $oldUniversities = DB::connection('old_db')->table('uni_data')->get();
        
        foreach ($oldUniversities as $oldUni) {
            // Check if university already exists in new DB
            $exists = DB::table('universities')->where('id', $oldUni->uni_id)->exists();
            
            if (!$exists) {
                DB::table('universities')->insert([
                    'id' => $oldUni->uni_id,
                    'name' => $oldUni->uni_name,
                    'address' => '', // Default empty as no address in old schema
                    'state' => '', // Default empty as no state in old schema
                    'country' => '', // Default empty as no country in old schema
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        
        $this->command->info('Universities migration completed.');
    }
    
    /**
     * Migrate users from initkey_rid to users table
     */
    // private function migrateUsers(): void
    // {
    //     $oldUsers = DB::connection('old_db')->table('initkey_rid')->get();
        
    //     foreach ($oldUsers as $oldUser) {
    //         // Split username into first and last name
    //         $nameParts = $this->splitUsername($oldUser->username);
            
    //         // Check if user already exists
    //         $exists = DB::table('users')->where('id', $oldUser->uuid)->exists();
            
    //         if (!$exists) {
    //             // Map university ID
    //             $universityId = $oldUser->uni ?? DB::connection('old_db')->table('uni_data')->first()?->uni_id ?? null;
                
    //             // Insert into users table
    //             DB::table('users')->insert([
    //                 'id' => $oldUser->uuid,
    //                 'first_name' => $nameParts['first_name'],
    //                 'last_name' => $nameParts['last_name'],
    //                 'phone' => $oldUser->phone ?? '',
    //                 'email' => $oldUser->email,
    //                 'profile_picture' => $oldUser->imageUrl,
    //                 'university_id' => $universityId,
    //                 'email_verified_at' => $oldUser->activation == 1 ? Carbon::now() : null,
    //                 'password' => $oldUser->password, // Assuming already hashed
    //                 'created_at' => $oldUser->dateCreated ?? Carbon::now(),
    //                 'updated_at' => Carbon::now(),
    //             ]);
                
    //             // Handle password reset tokens if they exist
    //             if (!empty($oldUser->password_reset_token)) {
    //                 DB::table('password_reset_tokens')->insert([
    //                     'email' => $oldUser->email,
    //                     'token' => $oldUser->password_reset_token,
    //                     'created_at' => $oldUser->reset_token_set_date ?? Carbon::now(),
    //                 ]);
    //             }
    //         }
    //     }
        
    //     $this->command->info('Users migration completed.');
    // }
    
//     private function migrateUsers(): void
// {
//     $oldUsers = DB::connection('old_db')->table('initkey_rid')->get();
//     $skipped = 0;

//     foreach ($oldUsers as $oldUser) {
//         // Generate or fallback to a UUID
//         $userId = Str::isUuid($oldUser->uuid) ? $oldUser->uuid : Str::uuid()->toString();

//         // Check for unique constraints
//         $emailExists = DB::table('users')->where('email', $oldUser->email)->exists();
//         $phoneExists = DB::table('users')->where('phone', $oldUser->phone)->exists();

//         if ($emailExists || $phoneExists) {
//             $skipped++;
//             continue; // Skip duplicates
//         }

//         $nameParts = $this->splitUsername($oldUser->username);

//         // Map university ID (fallback to any available one if missing)
//         $universityId = $oldUser->uni 
//             ?? DB::connection('old_db')->table('uni_data')->first()?->uni_id 
//             ?? DB::table('universities')->first()?->id;

//         // Insert user
//         DB::table('users')->insert([
//             'id' => $userId,
//             'first_name' => $nameParts['first_name'],
//             'last_name' => $nameParts['last_name'],
//             'phone' => $oldUser->phone ?? '0000000000', // Placeholder if missing
//             'email' => $oldUser->email,
//             'profile_picture' => $oldUser->imageUrl,
//             'university_id' => $universityId,
//             'email_verified_at' => $oldUser->activation == 1 ? Carbon::now() : null,
//             'password' => $oldUser->password, // Assuming hashed
//             'created_at' => $oldUser->dateCreated ?? Carbon::now(),
//             'updated_at' => Carbon::now(),
//         ]);

//         // Handle password reset token
//         if (!empty($oldUser->password_reset_token)) {
//             DB::table('password_reset_tokens')->insert([
//                 'email' => $oldUser->email,
//                 'token' => $oldUser->password_reset_token,
//                 'created_at' => $oldUser->reset_token_set_date ?? Carbon::now(),
//             ]);
//         }
//     }

//     $this->command->info('Users migration completed.');
//     $this->command->warn("Skipped $skipped user(s) due to duplicate email or phone.");
// }
private function migrateUsers(): void
{
    $oldUsers = DB::connection('old_db')->table('initkey_rid')->get();
    $skipped = 0;

    foreach ($oldUsers as $oldUser) {
        // Use original user_id from old DB
        $userId = $oldUser->user_id;

        // Check for duplicate email only
        $emailExists = DB::table('users')->where('email', $oldUser->email)->exists();
        if ($emailExists) {
            $skipped++;
            continue;
        }

        // Ensure unique phone number
        $phone = $oldUser->phone ?? '0000000000';
        $basePhone = preg_replace('/[^0-9]/', '', $phone);
        $originalPhone = $basePhone;

        $counter = 1;
        while (DB::table('users')->where('phone', $basePhone)->exists()) {
            $basePhone = $originalPhone . $counter;
            $counter++;
        }

        $nameParts = $this->splitUsername($oldUser->username);

        // Map university ID
        $universityId = $oldUser->uni 
            ?? DB::connection('old_db')->table('uni_data')->first()?->uni_id 
            ?? DB::table('universities')->first()?->id;

        // Insert user with preserved ID
        DB::table('users')->insert([
            'id' => $userId,
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'phone' => $basePhone,
            'email' => $oldUser->email,
            'profile_picture' => $oldUser->imageUrl,
            'university_id' => '161',
            'email_verified_at' => $oldUser->activation == 1 ? Carbon::now() : null,
            'password' => $oldUser->password,
            'created_at' => $oldUser->dateCreated ?? Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Insert password reset token if it exists
        if (!empty($oldUser->password_reset_token)) {
            DB::table('password_reset_tokens')->insert([
                'email' => $oldUser->email,
                'token' => $oldUser->password_reset_token,
                'created_at' => $oldUser->reset_token_set_date ?? Carbon::now(),
            ]);
        }
    }

    $this->command->info('Users migration completed.');
    $this->command->warn("Skipped $skipped user(s) due to duplicate email.");
}

    /**
     * Migrate stores from shopdetails to stores table
     */
    // private function migrateStores(): void
    // {
    //     $oldShops = DB::connection('old_db')->table('shopdetails')->get();
        
    //     foreach ($oldShops as $oldShop) {
    //         // Get seller details from initkey_rid
    //         $seller = DB::connection('old_db')->table('initkey_rid')
    //             ->where('user_id', $oldShop->seller_id)
    //             ->first();
    // //             $seller = DB::connection('old_db')->table('initkey_rid')
    // // ->where('id', $oldShop->seller_id)
    // // ->first();
    // // $seller = DB::connection('old_db')->table('initkey_rid')
    // // ->where('user_id', $oldShop->seller_id)
    // // ->first();
                
    //         // Get university ID
    //         $universityId = DB::connection('old_db')->table('uni_data')
    //             ->where('uni_name', $oldShop->uni_name)
    //             ->value('uni_id');
                
    //         if (!$seller || !$universityId) {
    //             continue; // Skip if required relations not found
    //         }
            
    //         // Check if store already exists
    //         $exists = DB::table('stores')->where('id', $oldShop->shop_id)->exists();
            
    //         if (!$exists) {
    //             // Determine store type
    //             $storeType = !empty($oldShop->food_store_id) ? 'food' : 'regular';
                
    //             // Map status
    //             $status = ($oldShop->shop_status ?? 'active') == 'active' ? 'is_active' : 'is_inactive';
                
    //             // Insert into stores table
    //             DB::table('stores')->insert([
    //                 'id' => $oldShop->shop_id,
    //                 'user_id' => $seller->uuid,
    //                 'university_id' => $universityId,
    //                 'type' => $storeType,
    //                 'name' => $storeType == 'food' ? $oldShop->food_store_name : $oldShop->shop_name,
    //                 'image' => $storeType == 'food' ? $oldShop->food_shop_image_url : $oldShop->shop_image_url,
    //                 'description' => $storeType == 'food' ? $oldShop->food_store_desc : $oldShop->shop_desc,
    //                 'status' => $status,
    //                 'next_payment_due' => null,
    //                 'created_at' => $oldShop->datecreated ?? Carbon::now(),
    //             ]);
    //         }
    //     }
        
    //     $this->command->info('Stores migration completed.');
    // }
    
//     private function migrateStores(): void
// {
//     $oldShops = DB::connection('old_db')->table('shopdetails')->get();

//     foreach ($oldShops as $oldShop) {
//         // Get seller by seller_id → initkey_rid.id
//         $seller = DB::connection('old_db')->table('initkey_rid')
//             ->where('id', $oldShop->seller_id)
//             ->first();

//         // Get university ID
//         $universityId = DB::connection('old_db')->table('uni_data')
//             ->where('uni_name', $oldShop->uni_name)
//             ->value('uni_id');

//         if (!$seller || !$seller->uuid || !$universityId) {
//             continue;
//         }

//         $storeId = $oldShop->shop_id;

//         // Check if store already exists
//         $exists = DB::table('stores')->where('id', $storeId)->exists();

//         if (!$exists) {
//             $storeType = !empty($oldShop->food_store_id) ? 'food' : 'regular';
//             $status = ($oldShop->shop_status ?? 'active') == 'active' ? 'is_active' : 'is_inactive';

//             DB::table('stores')->insert([
//                 'id' => $storeId,
//                 'user_id' => $seller->uuid,
//                 'university_id' => $universityId,
//                 'type' => $storeType,
//                 'name' => $storeType == 'food' ? $oldShop->food_store_name : $oldShop->shop_name,
//                 'image' => $storeType == 'food' ? $oldShop->food_shop_image_url : $oldShop->shop_image_url,
//                 'description' => $storeType == 'food' ? $oldShop->food_store_desc : $oldShop->shop_desc,
//                 'status' => $status,
//                 'next_payment_due' => null,
//                 'created_at' => $oldShop->datecreated ?? Carbon::now(),
//             ]);
//         }
//     }

//     $this->command->info('Stores migration completed.');
// }
//     private function migrateStores(): void
// {
//     $oldShops = DB::connection('old_db')->table('shopdetails')->get();
    
//     foreach ($oldShops as $oldShop) {
//         // Get seller details from initkey_rid using the correct shop_id
//         $seller = DB::connection('old_db')->table('initkey_rid')
//             ->where('shop_id', $oldShop->shop_id)  // Correctly map with shop_id
//             ->first();
        
//         // Get university ID
//         $universityId = DB::connection('old_db')->table('uni_data')
//             ->where('uni_name', $oldShop->uni_name)
//             ->value('uni_id');
        
//         if (!$seller || !$seller->uuid || !$universityId) {
//             continue; // Skip if required relations not found
//         }
        
//         // Check if store already exists
//         $exists = DB::table('stores')->where('id', $oldShop->shop_id)->exists();
        
//         if (!$exists) {
//             // Determine store type
//             $storeType = !empty($oldShop->food_store_id) ? 'food' : 'regular';
            
//             // Map status
//             $status = ($oldShop->shop_status ?? 'active') == 'active' ? 'is_active' : 'is_inactive';
            
//             // Insert into stores table
//             DB::table('stores')->insert([
//                 'id' => $oldShop->shop_id,
//                 'user_id' => $seller->uuid,  // Use seller's uuid
//                 'university_id' => $universityId,
//                 'type' => $storeType,
//                 'name' => $storeType == 'food' ? $oldShop->food_store_name : $oldShop->shop_name,
//                 'image' => $storeType == 'food' ? $oldShop->food_shop_image_url : $oldShop->shop_image_url,
//                 'description' => $storeType == 'food' ? $oldShop->food_store_desc : $oldShop->shop_desc,
//                 'status' => $status,
//                 'next_payment_due' => null,
//                 'created_at' => $oldShop->datecreated ?? Carbon::now(),
//             ]);
//         }
//     }
    
//     $this->command->info('Stores migration completed.');
// }
// private function migrateStores(): void
// {
//     $oldShops = DB::connection('old_db')->table('shopdetails')->get();

//     foreach ($oldShops as $oldShop) {
//         // Get seller by seller_id → initkey_rid.id
//         $seller = DB::connection('old_db')->table('initkey_rid')
//             ->where('id', $oldShop->seller_id)
//             ->first();

//         // Get university ID
//         $universityId = DB::connection('old_db')->table('uni_data')
//             ->where('uni_name', $oldShop->uni_name)
//             ->value('uni_id');

//         if (!$seller || !$seller->uuid || !$universityId) {
//             continue;
//         }

//         $storeId = $oldShop->shop_id;

//         // Check if store already exists
//         $exists = DB::table('stores')->where('id', $storeId)->exists();

//         if (!$exists) {
//             $storeType = !empty($oldShop->food_store_id) ? 'food' : 'regular';
//             $status = ($oldShop->shop_status ?? 'active') == 'active' ? 'is_active' : 'is_inactive';

//             DB::table('stores')->insert([
//                 'id' => $storeId,
//                 'user_id' => $seller->uuid,
//                 'university_id' => $universityId,
//                 'type' => $storeType,
//                 'name' => $storeType == 'food' ? $oldShop->food_store_name : $oldShop->shop_name,
//                 'image' => $storeType == 'food' ? $oldShop->food_shop_image_url : $oldShop->shop_image_url,
//                 'description' => $storeType == 'food' ? $oldShop->food_store_desc : $oldShop->shop_desc,
//                 'status' => $status,
//                 'next_payment_due' => null,
//                 'created_at' => $oldShop->datecreated ?? Carbon::now(),
//             ]);
//         }
//     }

//     $this->command->info('Stores migration completed.');
// }

    /**
     * Migrate categories from product data
     */
    private function migrateCategories(): void
    {
        // First, get unique categories from the product table
        $productCategories = DB::connection('old_db')->table('products')
            ->select('product_cat')
            ->distinct()
            ->whereNotNull('product_cat')
            ->get()
            ->pluck('product_cat');
            
        // Create categories in the new DB
        foreach ($productCategories as $category) {
            if (!empty($category)) {
                $exists = DB::table('categories')
                    ->where('name', $category)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('categories')->insert([
                        'id' => Str::uuid()->toString(),
                        'name' => $category,
                        'store_type' => 'regular', // Default to regular
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
        
        $this->command->info('Categories migration completed.');
    }
 private function migrateStores(): void
{
    $oldShops = DB::connection('old_db')->table('shopdetails')->get();

    foreach ($oldShops as $oldShop) {
        // Use user_id directly instead of uuid
        $seller = DB::connection('old_db')->table('initkey_rid')
            ->where('user_id', $oldShop->seller_id)
            ->first();

        // Get university ID
        $universityId = DB::connection('old_db')->table('uni_data')
            ->where('uni_name', $oldShop->uni_name)
            ->value('uni_id');

        if (!$seller || !$universityId) {
            continue; // Skip if seller or university not found
        }

        // Check if user exists in the new users table
        $newUserExists = DB::table('users')->where('id', $seller->user_id)->exists();

        if (!$newUserExists) {
            continue; // Skip if the user doesn't exist in the new system
        }

        // Check if store already exists
        $exists = DB::table('stores')->where('id', $oldShop->shop_id)->exists();

        if (!$exists) {
            // Determine store type
            $storeType = !empty($oldShop->food_store_id) ? 'food' : 'regular';

            // Map status
            $status = ($oldShop->shop_status ?? 'active') === 'active' ? 'is_active' : 'is_inactive';

            // Insert store
            DB::table('stores')->insert([
                'id' => $oldShop->shop_id,
                'user_id' => $seller->user_id,
                'university_id' => $universityId,
                'type' => $storeType,
                'name' => $storeType === 'food' ? $oldShop->food_store_name : $oldShop->shop_name,
                'image' => $storeType === 'food' ? $oldShop->food_shop_image_url : $oldShop->shop_image_url,
                'description' => $storeType === 'food' ? $oldShop->food_store_desc : $oldShop->shop_desc,
                'status' => $status,
                'next_payment_due' => null,
                'created_at' => $oldShop->datecreated ?? Carbon::now(),
            ]);
        }
    }

    $this->command->info('Stores migration completed.');
}


    /**
     * Migrate products from product table to products and product_images tables
     */
    // private function migrateProducts(): void
    // {
    //     $oldProducts = DB::connection('old_db')->table('products')->get();
        
    //     foreach ($oldProducts as $oldProduct) {
    //         // Get category ID from the name
    //         $categoryId = DB::table('categories')
    //             ->where('name', $oldProduct->product_cat)
    //             ->value('id');
                
    //         if (!$categoryId) {
    //             // Create a default category if needed
    //             $categoryId = Str::uuid()->toString();
    //             DB::table('categories')->insert([
    //                 'id' => $categoryId,
    //                 'name' => $oldProduct->product_cat ?? 'Uncategorized',
    //                 'store_type' => 'regular',
    //                 'created_at' => Carbon::now(),
    //                 'updated_at' => Carbon::now(),
    //             ]);
    //         }
            
    //         // Check if product already exists
    //         $exists = DB::table('products')->where('id', $oldProduct->product_id)->exists();
            
    //         if (!$exists) {
    //             // Insert product
    //             DB::table('products')->insert([
    //                 'id' => $oldProduct->product_id,
    //                 'store_id' => $oldProduct->shop_id,
    //                 'category_id' => $categoryId,
    //                 'name' => $oldProduct->product_name,
    //                 'description' => $oldProduct->product_desc,
    //                 'price' => $oldProduct->amount ?? 0,
    //                 'status' => 'active',
    //                 'created_at' => Carbon::now(),
    //                 'updated_at' => Carbon::now(),
    //             ]);
                
    //             // Handle product images
    //             $this->insertProductImage($oldProduct->product_id, $oldProduct->product_img1);
    //             $this->insertProductImage($oldProduct->product_id, $oldProduct->product_img2);
    //             $this->insertProductImage($oldProduct->product_id, $oldProduct->product_img3);
    //             $this->insertProductImage($oldProduct->product_id, $oldProduct->product_img4);
    //             $this->insertProductImage($oldProduct->product_id, $oldProduct->product_img5);
    //         }
    //     }
        
    //     $this->command->info('Products migration completed.');
    // }
    
    private function migrateProducts(): void
{
    $oldProducts = DB::connection('old_db')->table('products')->get();

    foreach ($oldProducts as $oldProduct) {
        // Get category ID from the name
        $categoryId = DB::table('categories')
            ->where('name', $oldProduct->product_cat)
            ->value('id');

        if (!$categoryId) {
            // Create a default category if needed
            $categoryId = Str::uuid()->toString();
            DB::table('categories')->insert([
                'id' => $categoryId,
                'name' => $oldProduct->product_cat ?? 'Uncategorized',
                'store_type' => 'regular',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Generate a UUID for the product ID instead of using the long string
        $newProductId = Str::uuid()->toString();

        // Check if product already exists using name + store combo
        $exists = DB::table('products')
            ->where('name', $oldProduct->product_name)
            ->where('store_id', $oldProduct->shop_id)
            ->exists();

        if (!$exists) {
            // Insert product with UUID
            DB::table('products')->insert([
                'id' => $newProductId,
                'store_id' => $oldProduct->shop_id,
                'category_id' => $categoryId,
                'name' => $oldProduct->product_name,
                'description' => $oldProduct->product_desc,
                'price' => $oldProduct->amount ?? 0,
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Handle product images
            $this->insertProductImage($newProductId, $oldProduct->product_img1);
            $this->insertProductImage($newProductId, $oldProduct->product_img2);
            $this->insertProductImage($newProductId, $oldProduct->product_img3);
            $this->insertProductImage($newProductId, $oldProduct->product_img4);
            $this->insertProductImage($newProductId, $oldProduct->product_img5);
        }
    }

    $this->command->info('Products migration completed.');
}

    /**
     * Migrate product requests from product_requests table
     */
    private function migrateProductRequests(): void
    {
        $oldRequests = DB::connection('old_db')->table('product_requests')->get();
        
        foreach ($oldRequests as $oldRequest) {
            // Get user ID from old tables
            $userId = DB::connection('old_db')->table('initkey_rid')
                ->where('user_id', $oldRequest->request_id)
                ->value('uuid');
                
            if (!$userId) {
                continue; // Skip if user not found
            }
            
            // Get or create a default category
            $categoryId = DB::table('categories')
                ->where('name', 'Product Requests')
                ->value('id');
                
            if (!$categoryId) {
                $categoryId = Str::uuid()->toString();
                DB::table('categories')->insert([
                    'id' => $categoryId,
                    'name' => 'Product Requests',
                    'store_type' => 'regular',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            
            // Generate a new UUID for this request
            $requestId = Str::uuid()->toString();
            
            // Insert product request
            DB::table('product_requests')->insert([
                'id' => $requestId,
                'user_id' => $userId,
                'category_id' => $categoryId,
                'name' => $oldRequest->product_name,
                'description' => $oldRequest->desc,
                'created_at' => $oldRequest->date ? Carbon::parse($oldRequest->date) : Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Insert request image if available
            if (!empty($oldRequest->img)) {
                DB::table('product_request_images')->insert([
                    'product_request_id' => $requestId,
                    'path' => $oldRequest->img,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        
        $this->command->info('Product requests migration completed.');
    }
    
    /**
     * Helper function to insert product image
     */
    private function insertProductImage($productId, $imagePath): void
    {
        if (!empty($imagePath)) {
            DB::table('product_images')->insert([
                'id' => Str::uuid()->toString(),
                'product_id' => $productId,
                'image_path' => $imagePath,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
    
    /**
     * Helper function to split username into first and last name
     */
    private function splitUsername($username): array
    {
        $parts = explode(' ', $username, 2);
        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
        ];
    }
}