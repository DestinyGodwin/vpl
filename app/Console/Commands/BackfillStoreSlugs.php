<?php

namespace App\Console\Commands;

use App\Models\Store;
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

    public function handle() {
        $stores = Store::whereNull( 'slug' )->get();

        foreach ( $stores as $store ) {
            $store->slug = null;
            $store->save();
            $this->info( "Slug generated for store: {$store->name} => {$store->slug}" );
        }

        $this->info( 'Backfill complete.' );
    }
}
