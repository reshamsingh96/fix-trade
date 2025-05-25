<?php

namespace App\Console\Commands;

 
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class EnvUpdateCmd extends Command
{
    const GOOGLE_REDIRECT_URI = "";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'env:up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Update ENV Value And Add ENV';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $cloudinary_name="drbqof1ct";
        $cloudinary_api_key="438828449343415";
        $cloudinary_secret_key="IQ4tTCGG7-dNB1HaW8B9qHjPo1k";
        $cloudinary_url="cloudinary://438828449343415:IQ4tTCGG7-dNB1HaW8B9qHjPo1k@drbqof1ct";

        $this->info('ENV Update Cmd Staring....');
        // DotenvEditor::setKey('FILE_DESTINATION', 'public');
        DotenvEditor::setKey('CLOUDINARY_NAME', $cloudinary_name);
        DotenvEditor::setKey('CLOUDINARY_API_KEY', $cloudinary_api_key);
        DotenvEditor::setKey('CLOUDINARY_SECRET_KEY', $cloudinary_secret_key);
        DotenvEditor::setKey('CLOUDINARY_URL', $cloudinary_url);
        DotenvEditor::save();

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        
        $this->info('ENV Updated Successfully');
    }
}
