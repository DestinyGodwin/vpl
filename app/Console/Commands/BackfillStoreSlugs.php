<?php

namespace App\Console\Commands;

use App\Models\Store;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class BackfillStoreSlugs extends Command {
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'app:backfill-store-slugs';

    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'Generate slugs for stores without slugs';

    /**
    * Execute the console command.
    */

     public function handle()
    {
        $stores = Store::whereNull('slug')->orWhere('slug', '')->get();
        
        $bar = $this->output->createProgressBar($stores->count());
        
        foreach ($stores as $store) {
            $slug = Str::slug($store->name);
            $counter = 1;
            
            while (Store::where('slug', $slug)->where('id', '!=', $store->id)->exists()) {
                $slug = Str::slug($store->name) . '-' . $counter++;
            }
            
            $store->update(['slug' => $slug]);
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nUpdated {$stores->count()} stores");
    }

}
