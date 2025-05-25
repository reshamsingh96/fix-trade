<?php

namespace App\Console\Commands;

use App\Constants\CommonConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
# use Nwidart\Modules\Facades\Module;
use Jackiedo\DotenvEditor\DotenvEditor;

class Install extends Command
{
    protected $signature = 'fix:trade {db=refresh}';
    protected $description = 'Migrates Database, Seeds Database, Generates Keys, Sets Environment, Configures Reverb, and more.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $type = $this->argument('db');
        $acceptedArgs = ['fresh', 'refresh'];

        if (!in_array($type, $acceptedArgs)) {
            return $this->error('Invalid Argument: ' . $type);
        }

        $this->line('📦 Starting Project Installation with DB Mode: ' . strtoupper($type));

        # STEP 1: Generate Application Key
        $this->generateAppKey();

        # STEP 2: Optional - Clear Export Folder (if fresh)
        if ($type === "fresh") {
            $this->clearExportsFolder();
        }

        # STEP 3: Migrate Database
        $this->setupDatabase($type);

        # STEP 5: Import Country, State, City
        $this->setupCountry();
        
        # STEP 4: Seed the Database
        $this->seedDatabase();

        # STEP 6: Create Storage Symlink
        $this->storageLink();

        # STEP 7: Clear Config Cache
        $this->clearCache();

        # STEP 8: Reverb Setup (Real-time Server)
        $this->reverbInstall();

        # STEP 9: Run env:up Command
        $this->envUpdateCmd();

        $this->info('🎉 Project Installation Complete!');
    }

    # 1. Generate Application Key
    protected function generateAppKey()
    {
        $this->line('🔐 Generating Application Key...');
        $this->call('key:generate', ['--force' => true]);
        $this->info('✅ App Key generated');
    }

    # 2. Optional - Clear Export Folder (if fresh)
    protected function clearExportsFolder()
    {
        $folders = [
            'exports' => 'Exports folder',
        ];

        foreach ($folders as $folder => $label) {
            $path = storage_path("app/public/{$folder}");

            if (File::exists($path)) {
                File::cleanDirectory($path);
                $this->info("🧹 {$label} cleared successfully.");
            } else {
                $this->warn("⚠️ {$label} does not exist, skipping...");
            }
        }
    }

    # 3. Migrate Database
    protected function setupDatabase($type)
    {
        $this->line('🛠️ Running database migration: ' . strtoupper($type));

        if ($type === "fresh") {
            $this->call('migrate:fresh', ['--force' => true]);
        } else {
            $this->call('migrate:refresh');
        }

        $this->info('✅ Database migration complete');
    }

        /**
     * Imports Country, State, and City data using custom Artisan commands.
     *
     * This is useful to pre-fill geographical data for forms, user addresses, etc.
     * Make sure `import:country`, `import:state`, and `import:city` commands exist and are registered.
     *
     * @return void
     */
    protected function setupCountry()
    {
        $this->info('🌍 Importing Country, State, and City data...');

        $this->call('import:country');
        $this->call('import:state');
        $this->call('import:city');

        $this->info('✅ Country, State, and City data imported.');
    }

    # 4: Seed the Database
    protected function seedDatabase()
    {
        $this->line('🌱 Seeding Database...');
        $this->call('db:seed');
        $this->info('✅ Database Seeding completed');
    }

    # 6: Seed the Database
    protected function storageLink()
    {
        $this->call('storage:link');
        $this->info('🔗 Storage linked successfully');
    }

    # 7. Clear Config Cache
    protected function clearCache()
    {
        $this->info('🧼 Clearing config cache...');
        $this->call('config:clear');
        $this->call('config:cache');
        $this->call('optimize:clear');
    }

    # 8. Reverb Setup (Real-time Server)  install cmd
    /**
     * Install and configure Laravel Reverb for real-time broadcasting.
     *
     * This method:
     * - Publishes Reverb config files (if not already published).
     * - Prompts the user to hit Enter at steps for better CLI experience.
     * - Installs Reverb setup.
     */
    protected function reverbInstall()
    {
        $this->info('⚡ Setting up Laravel Reverb...');

        # Step 1: Remove existing Reverb keys from .env
        $this->removeReverbKey();

        # Optional: Pause before installing
        $this->line('🔧 Installing Reverb...');
        $this->info('👉 Press Enter to continue...');
        fgets(STDIN);

        # Step 2: Run Reverb install
        $this->call('reverb:install');

        $this->info('✅ Reverb setup complete!');
    }

    /**
     * Remove all existing Reverb-related keys from the .env file.
     */
    public function removeReverbKey()
    {
        $reverbKeys = [
            'REVERB_APP_ID',
            'REVERB_APP_KEY',
            'REVERB_APP_SECRET',
            'REVERB_HOST',
            'REVERB_PORT',
            'REVERB_SCHEME',
            'VITE_REVERB_APP_KEY',
            'VITE_REVERB_HOST',
            'VITE_REVERB_PORT',
            'VITE_REVERB_SCHEME',
        ];

        # ✅ Resolve via Laravel container
        $env = app(\Jackiedo\DotenvEditor\DotenvEditor::class);

        $this->info("🔍 Checking and cleaning Reverb keys from .env...");

        # Load .env file
        $env->load();

        foreach ($reverbKeys as $key) {
            if ($env->keyExists($key)) {
                $env->deleteKey($key);
                $this->info("🗑️ Removed: $key");
            }
        }

        # Save updated .env
        $env->save();

        $this->info('🎉 All Reverb keys removed successfully!');
    }

    # 9. Run env:up Command
    protected function envUpdateCmd()
    {
        $this->call('env:up');
        $this->info('🌍 .env updated successfully');
    }
}
